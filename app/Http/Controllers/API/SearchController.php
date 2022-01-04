<?php

namespace App\Http\Controllers\API;

use Exception;
use FollowoutHelper;
use GooglePlacesHelper;
use PaymentHelper;
use Str;
use Validator;
use App\User;
use App\Followout;
use App\PromoCode;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\FollowoutResource;
use App\Http\Resources\FollowoutCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function users(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'keyword' => 'nullable|string',
            'only_with_active_followouts' => 'nullable|in:true,false',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query['name'] = $request->input('name', null);
        $query['email'] = $request->input('email', null);
        $query['phone_number'] = $request->input('phone_number', null);
        $query['keyword'] = $request->input('keyword', null);
        $query['only_with_active_followouts'] = $request->input('only_with_active_followouts', 'false');

        $users = User::query()->with(User::$withProfile)->orderBy('created_at');

        if (is_null($authUser) || !$authUser->isAdmin()) {
            $users->public();
        }

        $result = collect([]);

        if (is_null($query['name']) && is_null($query['email']) && is_null($query['phone_number']) && is_null($query['keyword']) && $query['only_with_active_followouts'] === 'false') {
            $result = new UserCollection( UserResource::collection( $users->get() ) );
        } else {
            if ($query['only_with_active_followouts'] === 'true') {
                $users->whereHas('followouts', function ($query) {
                    $query->ongoingOrUpcoming()->whereIn('privacy_type', ['public', 'followers']);
                });

                $q = clone $users;

                $usersWithActiveFollowouts = new UserCollection( UserResource::collection( $q->get() ) );
                $result = $result->merge($usersWithActiveFollowouts);
            }

            if ($query['name']) {
                $q = clone $users;
                $q->where('name', 'like', '%' . $query['name'] . '%')->get();

                $usersByName = new UserCollection( UserResource::collection( $q->get() ) );
                $result = $result->merge($usersByName);
            }

            if ($query['email']) {
                $q = clone $users;
                $q->where('email', 'like', '%' . $query['email'] . '%');

                $usersByEmail = new UserCollection( UserResource::collection( $q->get() ) );
                $result = $result->merge($usersByEmail);
            }

            if ($query['phone_number']) {
                $q = clone $users;
                $q->where('phone_number', 'like', '%' . $query['phone_number'] . '%');

                $usersByPhoneNumber = new UserCollection( UserResource::collection( $q->get() ) );
                $result = $result->merge($usersByPhoneNumber);
            }

            if ($query['keyword']) {
                $q = clone $users;
                $q->where('keywords', 'like', '%' . $query['keyword'] . '%');

                $usersByKeyword = new UserCollection( UserResource::collection( $q->get() ) );
                $result = $result->merge($usersByKeyword);
            }

            $result = $result->unique();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'query' => $query,
                'users' => $result,
            ],
        ]);
    }

    public function followouts(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'timeframe' => 'nullable|string|in:upcoming,ongoing_or_upcoming,ongoing,all',
            'title' => 'nullable|string|max:128',
            'author_id' => 'nullable|string|exists:users,_id',
            'category_id' => 'nullable|string|exists:followout_categories,_id',
            'lat' => 'required_with:lng|lat|not_in:0',
            'lng' => 'required_with:lat|lng|not_in:0',
            'radius' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'take' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query['title'] = $request->input('title', null);
        $query['timeframe'] = $request->input('timeframe', 'upcoming');
        $query['author_id'] = $request->input('author_id', null);
        $query['category_id'] = $request->input('category_id', null);
        $query['lat'] = $request->input('lat') ? doubleval($request->input('lat')) : null;
        $query['lng'] = $request->input('lng') ? doubleval($request->input('lng')) : null;
        $query['radius'] = (int) $request->input('radius', 160934);
        $query['offset'] = (int) $request->input('offset', 0);
        $query['take'] = (int) $request->input('take', 1000);

        $with = [
            'author',
            'author.country',
            'author.avatars',
            'author.account_categories',
            'flyer',
            'pictures',
            'experience_categories',
        ];

        $followouts = Followout::query()->with($with);

        if ($query['timeframe'] === 'upcoming') {
            $followouts->upcoming();
        } else if ($query['timeframe'] === 'ongoing_or_upcoming') {
            $followouts->ongoingOrUpcoming();
        } else if ($query['timeframe'] === 'ongoing') {
            $followouts->ongoing();
        }

        if ($query['title']) {
            $followouts->where('title', 'like', '%' . $query['title'] . '%');
        }

        if ($query['lat'] && $query['lng']) {
            $coordinates = [$query['lng'], $query['lat']];

            $followouts->where('location', 'near', [
                '$geometry' => [
                    'type' => 'Point',
                    'coordinates' => $coordinates,
                ],
                '$maxDistance' => $query['radius'],
            ]);
        }

        if ($query['author_id']) {
            $followouts->where('author_id', $query['author_id']);
        }

        if ($query['category_id']) {
            $followouts->whereIn('followout_category_ids', [$query['category_id']]);
        }

        $followouts = $followouts->orderBy('created_at', 'desc')->offset($query['offset'])->take($query['take'])->get();

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        $followouts = new FollowoutCollection( FollowoutResource::collection( $followouts ) );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'query' => $query,
                'followouts' => $followouts,
            ],
        ]);
    }

    public function promoCode(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $query['promo_code'] = $request->input('promo_code', null);

        if (PaymentHelper::validatePromoCode($query['promo_code'], $authUser)) {
            $code = PromoCode::where('code', $query['promo_code'])->first();
        } else {
            $code = null;
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'query' => $query,
                'promo_code' => $code,
            ],
        ]);
    }

    public function places(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|lat|not_in:0',
            'lng' => 'required|lng|not_in:0',
            'type' => 'nullable|google_places_type',
            'radius' => 'nullable|integer|min:50|max:50000',
            'limit' => 'nullable|integer|in:20,40,60,80,100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lat = $request->input('lat');
        $lng = $request->input('lng');

        $options['type'] = $request->input('type', null);

        $places = GooglePlacesHelper::getNearbyPlaces($lat, $lng, $options, $request->input('radius', null), $request->input('limit', null));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'places' => $places,
            ],
        ]);
    }

    public function place(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'place_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $place = GooglePlacesHelper::getPlace($request->input('place_id'));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'place' => $place,
            ],
        ]);
    }
}
