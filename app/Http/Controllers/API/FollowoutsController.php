<?php

namespace App\Http\Controllers\API;

use Gate;
use Mail;
use Carbon;
use Validator;
use FollowoutHelper;
use App\Coupon;
use App\Followee;
use App\Followout;
use App\FollowoutCoupon;
use App\RewardProgramJob;
use App\User;
use App\Http\Resources\FollowoutResource;
use App\Http\Resources\FollowoutCollection;
use App\Http\Resources\FollowoutCouponResource;
use App\Http\Resources\FollowoutCouponCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FollowoutsController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $with = [
            'author',
            'author.country',
            'author.avatars',
            'author.account_categories',
            'coupon',
            'flyer',
            'pictures',
            'experience_categories',
        ];

        $validator = Validator::make($request->all(), [
            'mode' => 'nullable|string|in:all,regular,geo',
            'category_id' => 'nullable|string|exists:followout_categories,_id',
            'include_ongoing' => 'nullable|in:true,false',
            'lat' => 'required_with:lng|lat|not_in:0',
            'lng' => 'required_with:lat|lng|not_in:0',
            'radius' => 'nullable|integer|not_in:0',
        ]);

        $query['mode'] = $request->input('category_id', 'all');
        $query['category_id'] = $request->input('category_id', null);
        $query['include_ongoing'] = $request->input('include_ongoing', 'false');
        $query['lat'] = $request->input('lat') ? doubleval($request->input('lat')) : null;
        $query['lng'] = $request->input('lng') ? doubleval($request->input('lng')) : null;
        $query['radius'] = (int) $request->input('radius', 5000);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $followouts = Followout::query()->with($with);

        if ($query['mode'] === 'regular') {
            $followouts->notGeoCoupon();
        } elseif ($query['mode'] === 'geo') {
            $followouts->geoCoupon();
        }

        if ($query['include_ongoing'] === 'true') {
            $followouts->ongoingOrUpcoming();
        } else {
            $followouts->upcoming();
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

        if ($query['category_id']) {
            $followouts->whereIn('followout_category_ids', [$query['category_id']]);
        }

        $followouts = $followouts->get();

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        $followoutIds = $followouts->pluck('id');

        $followouts = new FollowoutCollection(
            FollowoutResource::collection(
                Followout::with($with)->whereIn('_id', $followoutIds)->orderBy('starts_at')->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'query' => $query,
                'followouts' => $followouts,
            ],
        ]);
    }

    public function feed(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $sourceIds = $authUser->follows->pluck('to_id')->toArray();

        $followouts = Followout::ongoingOrUpcoming()->whereIn('author_id', $sourceIds)->get();

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        $followoutIds = $followouts->pluck('id');

        $followouts = new FollowoutCollection(
            FollowoutResource::collection(
                Followout::with(Followout::$withAll)->whereIn('_id', $followoutIds)->orderBy('starts_at')->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followouts' => $followouts,
            ],
        ]);
    }

    public function show(Request $request, $followout)
    {
        $followout = Followout::with(Followout::$withAll)->find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $followout->incrementViews(1);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout' => new FollowoutResource(Followout::with(Followout::$withAll)->find($followout->id)),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'nullable|required_with_all:address,zip_code|max:100',
            'state' => 'nullable|max:100',
            'address' => 'nullable|required_with_all:city,zip_code|max:100',
            'zip_code' => 'nullable|required_with_all:city,address|max:12',
            'lat' => 'nullable|required_with:lng|lat|not_in:0',
            'lng' => 'nullable|required_with:lat|lng|not_in:0',
            'geohash' => 'nullable|string|max:255',
            'starts_at' => 'nullable|required_with:ends_at|date_format:' . config('followouts.datetime_format') . '|after_or_equal:-1 year|before_or_equal:+90 days|before_or_equal:ends_at',
            'ends_at' => 'nullable|required_with:starts_at|date_format:' . config('followouts.datetime_format') . '|after_or_equal:starts_at|before:+1 year',
            'timezone' => 'nullable|timezone',
        ]);

        // If we have a valid address we'll use that instead
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $additionalData = [];

        if ($request->filled('lat') && $request->filled('lng')) {
            $additionalData['lat'] = doubleval($request->input('lat'));
            $additionalData['lng'] = doubleval($request->input('lng'));
        }

        if ($request->filled('city') || $request->filled('address') || $request->filled('state') || $request->filled('zip_code')) {
            $additionalData['city'] = $request->input('city');
            $additionalData['state'] = $request->input('state');
            $additionalData['address'] = $request->input('address');
            $additionalData['zip_code'] = $request->input('zip_code');
        }

        $additionalData['geohash'] = $request->input('geohash');

        if ($request->filled('starts_at') && $request->filled('starts_at')) {
            $additionalData['timezone'] = $request->input('timezone', 'UTC');
            $additionalData['starts_at'] = $request->input('starts_at');
            $additionalData['ends_at'] = $request->input('ends_at');
        }

        $data = FollowoutHelper::getEmptyFollowoutTemplateData(auth()->guard('api')->user()->getKey(), $additionalData, true);

        // Merge data into request and redirect to actual save route
        $request = $request->merge($data);

        return $this->store($request);
    }

    public function store(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $virtualAddressRule = $request->input('is_virtual', null) ? 'required_with:is_virtual|url' : 'required_with:is_virtual';

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:2500',
            'experience_categories' => 'required|array|max:5',
            'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'is_virtual' => 'nullable',
            'virtual_address' => $virtualAddressRule,
            'city' => 'required_without:is_virtual|max:100',
            'state' => 'nullable|max:100',
            'address' => 'required_without:is_virtual|max:100',
            'zip_code' => 'required_without:is_virtual|max:12',
            'lat' => 'required_without:is_virtual|lat|not_in:0',
            'lng' => 'required_without:is_virtual|lng|not_in:0',
            'geohash' => 'nullable|string|max:255',
            'radius' => 'nullable|integer|min:1|max:10000',
            'starts_at' => 'required|date_format:' . config('followouts.datetime_format') . '|after_or_equal:-1 year|before_or_equal:+90 days|before_or_equal:ends_at',
            'ends_at' => 'required|date_format:' . config('followouts.datetime_format') . '|after_or_equal:starts_at|before:+1 year',
            'tickets_url' => 'nullable|url',
            'external_info_url' => 'nullable|url',
            'invites' => 'nullable|array',
            'invites.*' => 'required|email|distinct',
            'privacy_type' => 'required|in:public,private,followers',
            'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
            'picture1' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'picture2' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'picture3' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'timezone' => 'nullable|timezone',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tz = $request->input('timezone', 'UTC');

        $followout = new Followout;
        $followout->title = $request->input('title');
        $followout->description = $request->input('description');
        $followout->tickets_url = $request->input('tickets_url', null);
        $followout->external_info_url = $request->input('external_info_url', null);
        $followout->hash = hash('sha256', $authUser->id.time());
        $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $request->input('starts_at'), $tz)->tz('UTC');
        $followout->ends_at = Carbon::createFromFormat(config('followouts.datetime_format'), $request->input('ends_at'), $tz)->tz('UTC');

        if ($request->input('is_virtual', null)) {
            $followout->is_virtual = true;
            $followout->virtual_address = $request->input('virtual_address');

            // This would allow mobile app to find the followouts via geolocation search
            if ($request->filled('lat') && $request->filled('lng')) {
                if ($request->filled('city') && $request->filled('address') && $request->filled('zip_code')) {
                    $followout->city = $request->input('city');
                    $followout->state = $request->input('state');
                    $followout->address = $request->input('address');
                    $followout->zip_code = $request->input('zip_code');
                } else {
                    $followout->city = 'İskilip';
                    $followout->state = 'Çorum';
                    $followout->address = 'Beyoğlan';
                    $followout->zip_code = '19400';
                }

                $followout->lat = doubleval($request->input('lat'));
                $followout->lng = doubleval($request->input('lng'));
                $followout->geohash = $request->input('geohash');
                $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
            } else {
                // Use default geo address
                $followout->city = 'İskilip';
                $followout->state = 'Çorum';
                $followout->address = 'Beyoğlan';
                $followout->zip_code = '19400';
                $followout->lat = doubleval(40.866667);
                $followout->lng = doubleval(34.566667);
                $followout->geohash = 'sz0yew3q8c1';
                $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
            }
        } else {
            $followout->is_virtual = false;
            $followout->city = $request->input('city');
            $followout->state = $request->input('state', null);
            $followout->address = $request->input('address');
            $followout->zip_code = $request->input('zip_code');
            $followout->lat = doubleval($request->input('lat'));
            $followout->lng = doubleval($request->input('lng'));
            $followout->geohash = $request->input('geohash');
            $followout->radius = $request->input('radius', null);
            $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        }

        if ($followout->starts_at > $followout->ends_at) {
            $followout->ends_at = $followout->starts_at->addHour();
        } else if ($followout->starts_at->timestamp === $followout->ends_at->timestamp) {
            $utcOffset = now()->tz($tz)->utcOffset();

            $followout->starts_at = $followout->starts_at->utcOffset($utcOffset)->setTime(0, 0, 0);
            $followout->ends_at = $followout->ends_at->utcOffset($utcOffset)->setTime(23, 59, 59);
        }

        if ($authUser->isFollowhost() && $authUser->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser($authUser)->allows('set-followout-privacy-type-public')) {
            FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly($authUser->id);
        }

        if ($request->input('privacy_type') === 'public' && Gate::forUser($authUser)->denies('set-followout-privacy-type-public')) {
            $followout->privacy_type = 'followers';
        } else {
            $followout->privacy_type = $request->input('privacy_type');
        }

        $followout = $authUser->followouts()->save($followout);
        $followout->experience_categories()->attach($request->input('experience_categories'));
        $followout->author()->associate($authUser);
        $followout->save();

        if ($request->hasFile('flyer')) {
            $followout->saveFlyer($request->file('flyer'));
        }

        if ($request->hasFile('picture1')) {
            $followout->savePicture($request->file('picture1'));
        }

        if ($request->hasFile('picture2')) {
            $followout->savePicture($request->file('picture2'));
        }

        if ($request->hasFile('picture3')) {
            $followout->savePicture($request->file('picture3'));
        }

        if (!$authUser->isFollowhost()) {
            $followee = new Followee(['status' => 'accepted']);
            $followee = $followout->followees()->save($followee);
            $followee->user()->associate($authUser);
            $followee->save();
        }

        foreach ($request->input('invites', []) as $email) {
            $followout->inviteAttendee($email);
        }

        FollowoutHelper::makeDefaultFollowoutPublicIfPossible($followout->author->id);

        Mail::to($authUser)->send(new \App\Mail\YourFollowoutCreated);

        if (!$followout->isDefault() && !$followout->hasFlyer() && !$authUser->hasDefaultFlyer()) {
            $followout->saveLocationFlyer();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout' => new FollowoutResource(Followout::with(Followout::$withAll)->find($followout->getKey())),
            ],
        ]);
    }

    public function update(Request $request, $followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $hasAccess = $authUser->id === $followout->author->id || $authUser->isAdmin();
        $privacyTypeRule = $followout->reward_programs()->count() > 0 ? 'in:public,followers' : 'in:public,private,followers';

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($followout->onlyPrivacyIsEditable($authUser)) {
            $rules = [
                'privacy_type' => 'nullable|' . $privacyTypeRule,
            ];
        } else if ($followout->isReposted()) {
            $rules = [
                'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
                'removed_flyer' => 'nullable|array|max:1',
                'removed_flyer.*' => 'nullable|string|distinct',
            ];
        } else if ($followout->isDefault()) {
            $rules = [
                'description' => 'required|string|max:2500',
                'experience_categories' => 'required|array',
                'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
                'tickets_url' => 'nullable|url',
                'external_info_url' => 'nullable|url',
                'invites' => 'nullable|array',
                'invites.*' => 'required|email|distinct',
                'privacy_type' => 'required|'.$privacyTypeRule,
                'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
                'picture1' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture2' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture3' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'removed_flyer' => 'nullable|array|max:1',
                'removed_flyer.*' => 'nullable|string|distinct',
                'removed_pictures' => 'nullable|array|max:3',
                'removed_pictures.*' => 'nullable|string|distinct',
            ];
        } else {
            $virtualAddressRule = $request->input('is_virtual', null) ? 'required_with:is_virtual|url' : 'required_with:is_virtual';

            $rules = [
                'title' => 'nullable|string|max:128',
                'description' => 'nullable|string|max:2500',
                'experience_categories' => 'nullable|array',
                'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
                'tickets_url' => 'nullable|url',
                'external_info_url' => 'nullable|url',
                'is_virtual' => 'nullable',
                'virtual_address' => $virtualAddressRule,
                'city' => 'required_without:is_virtual|max:100',
                'state' => 'nullable|max:100',
                'address' => 'required_without:is_virtual|max:100',
                'zip_code' => 'required_without:is_virtual|max:12',
                'lat' => 'required_without:is_virtual|lat|not_in:0',
                'lng' => 'required_without:is_virtual|lng|not_in:0',
                'geohash' => 'nullable|string|max:255',
                'radius' => 'nullable|integer|min:1|max:10000',
                'starts_at' => 'nullable|date_format:' . config('followouts.datetime_format') . '|after_or_equal:-1 year|before_or_equal:+90 days|before_or_equal:ends_at',
                'ends_at' => 'nullable|date_format:' . config('followouts.datetime_format') . '|after_or_equal:starts_at|before:+1 year',
                'invites' => 'nullable|array',
                'invites.*' => 'required|email|distinct',
                'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
                'picture1' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture2' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture3' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'privacy_type' => 'nullable|' . $privacyTypeRule,
                'removed_flyer' => 'nullable|array|max:1',
                'removed_flyer.*' => 'nullable|string|distinct',
                'removed_pictures' => 'nullable|array|max:3',
                'removed_pictures.*' => 'nullable|string|distinct',
                'timezone' => 'nullable|timezone',
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tz = $request->input('timezone', 'UTC');

        if ($followout->onlyPrivacyIsEditable($authUser)) {
            if ($request->filled('privacy_type')) {
                if ($authUser->isFollowhost() && $authUser->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser($authUser)->allows('set-followout-privacy-type-public')) {
                    FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly($authUser->id);
                }

                if ($followout->isGeoCoupon()) {
                    if (Gate::forUser($authUser)->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                        FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly($authUser->id);
                        $followout->privacy_type = 'public';
                    } else {
                        $followout->privacy_type = 'followers';
                    }
                } else if ($request->input('privacy_type') === 'public' && Gate::forUser($authUser)->denies('set-followout-privacy-type-public')) {
                    $followout->privacy_type = 'followers';
                } else {
                    $followout->privacy_type = $request->input('privacy_type');
                }
            }
        } else {
            if (!$followout->isReposted() && !$followout->isDefault()) {
                if ($request->input('title', null)) {
                    $followout->title = $request->input('title');
                }

                if ($request->input('description', null)) {
                    $followout->description = $request->input('description');
                }

                if ($request->input('is_virtual', null)) {
                    $followout->is_virtual = true;
                    $followout->virtual_address = $request->input('virtual_address');

                    // This would allow mobile app to find the followouts via geolocation search
                    if ($request->filled('lat') && $request->filled('lng')) {
                        if ($request->filled('city') && $request->filled('address') && $request->filled('zip_code')) {
                            $followout->city = $request->input('city');
                            $followout->state = $request->input('state');
                            $followout->address = $request->input('address');
                            $followout->zip_code = $request->input('zip_code');
                        } else {
                            $followout->city = 'İskilip';
                            $followout->state = 'Çorum';
                            $followout->address = 'Beyoğlan';
                            $followout->zip_code = '19400';
                        }

                        $followout->lat = doubleval($request->input('lat'));
                        $followout->lng = doubleval($request->input('lng'));
                        $followout->geohash = $request->input('geohash');
                        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
                    } else {
                        // Use default geo address
                        $followout->city = 'İskilip';
                        $followout->state = 'Çorum';
                        $followout->address = 'Beyoğlan';
                        $followout->zip_code = '19400';
                        $followout->lat = doubleval(40.866667);
                        $followout->lng = doubleval(34.566667);
                        $followout->geohash = 'sz0yew3q8c1';
                        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
                    }
                } else {
                    $followout->is_virtual = false;

                    if ($request->input('city')) {
                        $followout->city = $request->input('city');
                    }

                    if ($request->input('state')) {
                        $followout->state = $request->input('state');
                    }

                    if ($request->input('address')) {
                        $followout->address = $request->input('address');
                    }

                    if ($request->input('zip_code')) {
                        $followout->zip_code = $request->input('zip_code');
                    }

                    if ($request->input('lat') && $request->input('lng')) {
                        $followout->lat = doubleval($request->input('lat'));
                        $followout->lng = doubleval($request->input('lng'));
                        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
                    }

                    if ($request->input('geohash')) {
                        $followout->geohash = $request->input('geohash');
                    }

                    if ($request->input('radius')) {
                        $followout->radius = $request->input('radius');
                    }
                }

                if ($request->input('tickets_url')) {
                    $followout->tickets_url = $request->input('tickets_url');
                }

                if ($request->input('external_info_url')) {
                    $followout->external_info_url = $request->input('external_info_url');
                }

                if ($request->input('starts_at')) {
                    $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $request->input('starts_at'));
                }

                if ($request->input('ends_at')) {
                    $followout->ends_at = Carbon::createFromFormat(config('followouts.datetime_format'), $request->input('ends_at'));
                }

                if ($followout->starts_at > $followout->ends_at) {
                    $followout->ends_at = $followout->starts_at->addHour();
                } else if ($followout->starts_at->timestamp === $followout->ends_at->timestamp) {
                    $followout->starts_at = $followout->starts_at->setTime(0, 0, 0);
                    $followout->ends_at = $followout->ends_at->setTime(23, 59, 59);
                }

                if ($request->filled('privacy_type')) {
                    if ($authUser->isFollowhost() && $authUser->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser($authUser)->allows('set-followout-privacy-type-public')) {
                        FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly($authUser->id);
                    }

                    if ($followout->isGeoCoupon()) {
                        if (Gate::forUser($authUser)->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                            FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly($authUser->id);
                            $followout->privacy_type = 'public';
                        } else {
                            $followout->privacy_type = 'followers';
                        }
                    } else if ($request->input('privacy_type') === 'public' && Gate::forUser($authUser)->denies('set-followout-privacy-type-public')) {
                        $followout->privacy_type = 'followers';
                    } else {
                        $followout->privacy_type = $request->input('privacy_type');
                    }
                }

                if ($request->input('experience_categories', null)) {
                    $followout->experience_categories()->detach();
                    $followout->experience_categories()->attach($request->input('experience_categories'));
                }

                $followout->save();
            } else if ($followout->isOngoing() || $followout->isDefault()) {
                if ($request->input('privacy_type', null)) {
                    if ($followout->isGeoCoupon()) {
                        if (Gate::forUser($authUser)->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                            FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly($authUser->id);
                            $followout->privacy_type = 'public';
                        } else {
                            $followout->privacy_type = 'followers';
                        }
                    } else if ($request->input('privacy_type') === 'public' && Gate::forUser($authUser)->denies('set-followout-privacy-type-public')) {
                        $followout->privacy_type = 'followers';
                    } else {
                        $followout->privacy_type = $request->input('privacy_type');
                    }
                }

                if ($request->input('description', null)) {
                    $followout->description = $request->input('description');
                }

                if ($request->input('tickets_url', null)) {
                    $followout->tickets_url = $request->input('tickets_url', null);
                }

                if ($request->input('external_info_url', null)) {
                    $followout->external_info_url = $request->input('external_info_url', null);
                }

                if ($request->input('experience_categories', null)) {
                    $followout->experience_categories()->detach();
                    $followout->experience_categories()->attach($request->input('experience_categories'));
                }

                $followout->save();
            }
        }

        if ($request->input('removed_flyer')) {
            $followout->deleteFlyer();
        }

        if ($request->hasFile('flyer')) {
            $followout->saveFlyer($request->file('flyer'));
        }

        if (!$followout->isReposted()) {
            if ($request->input('removed_pictures')) {
                $followout->deletePicturesById($request->input('removed_pictures'));
            }

            if ($request->hasFile('picture1')) {
                $followout->savePicture($request->file('picture1'), 0);
            }

            if ($request->hasFile('picture2')) {
                $followout->savePicture($request->file('picture2'), 1);
            }

            if ($request->hasFile('picture3')) {
                $followout->savePicture($request->file('picture3'), 2);
            }
        }

        $followout->is_edited = true;
        $followout->save();

        foreach ($request->input('invites', []) as $email) {
            $followout->inviteAttendee($email);
        }

        FollowoutHelper::makeDefaultFollowoutPublicIfPossible($followout->author->id);
        FollowoutHelper::syncAttributesForRepostedFollowouts($followout);

        if (!$followout->isDefault() && !$followout->hasFlyer() && !$authUser->hasDefaultFlyer()) {
            $followout->saveLocationFlyer();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout' => new FollowoutResource(Followout::with(Followout::$withAll)->find($followout->getKey())),
            ],
        ]);
    }

    public function destroy(Request $request, $followout)
    {
        $followout = Followout::find($followout);

        $authUser = auth()->guard('api')->user();

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if ($followout->isDefault()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Default Followout can\'t be deleted.',
            ], 403);
        }

        if ($followout->isReposted()) {
            if (!($authUser->isAdmin() ||
                  $followout->author->id === $authUser->id ||
                  $followout->getTopParentFollowout()->author->id === $authUser->id
              )) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied.',
                ], 403);
            }
        } else {
            if (!($authUser->isAdmin() || $followout->author->id === $authUser->id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Access denied.',
                ], 403);
            }
        }

        $followout->deleteFollowout();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function getAuthor(Request $request, $followout)
    {
        $followout = Followout::with('author')->find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access defined.',
            ], 403);
        }

        return redirect()->action('API\UsersController@show', ['user' => $followout->author->id]);
    }

    public function getCheckins(Request $request, $followout)
    {
        $followout = Followout::with('checkins')->find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'checkins' => $followout->checkins,
            ],
        ]);
    }

    public function getFollowees(Request $request, $followout)
    {
        $onlyAccepted = $request->input('only_accepted', null);

        $followout = Followout::with('followees', 'accepted_followees')->find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($onlyAccepted === 'true') {
            $followees = $followout->accepted_followees;
        } else {
            $followees = $followout->followees;
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followees' => $followees,
            ],
        ]);
    }

    public function checkin(Request $request, $followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if (!$followout->isUpcomingOrOngoing()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This followout is archived.',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,enter,exit',
            'coupon_entered' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $request->input('status');

        if ($followout->hasCheckin($authUser->id, $status)) {
            return response()->json([ 'status' => 'OK' ]);
        }

        $checkin = $followout->checkins()->create([
            'status' => $status,
            'coupon_entered' => (bool) $request->input('coupon_entered')
        ]);

        $checkin->user()->associate($authUser);
        $checkin->save();

        if ($status === 'enter') {
            $followout->author->notify(new \App\Notifications\NewCheckin($followout));
        }

        FollowoutHelper::handleNewCheckin($followout);

        return response()->json([ 'status' => 'OK' ]);
    }

    public function getFavorited(Request $request, $followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $favorited = $followout->favorited;

        return response()->json([
            'status' => 'OK',
            'data' => [
                'favorited' => $favorited,
            ],
        ]);
    }

    public function inviteAttendees(Request $request, $followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if ($followout->author->id !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'users' => 'nullable|array',
            'users.*' => 'required|exists:users,_id|distinct',
            'emails' => 'nullable|array',
            'emails.*' => 'required|email|distinct',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'required|phone_number_int|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $invitedCount = 0;
        $failedInvitesCount = 0;

        // Send notifications to registered users
        foreach ($request->input('users', []) as $email) {
            $result = $followout->inviteAttendee($email, 'user');
            $result === true ? $invitedCount++ : $failedInvitesCount++;
        }

        // Send email notifications
        foreach ($request->input('emails', []) as $email) {
            $result = $followout->inviteAttendee($email, 'email');
            $result === true ? $invitedCount++ : $failedInvitesCount++;
        }

        // Send SMS notifications
        foreach ($request->input('phone_numbers', []) as $email) {
            $result = $followout->inviteAttendee($email, 'sms');
            $result === true ? $invitedCount++ : $failedInvitesCount++;
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'invited_count' => $invitedCount,
                'failed_invites_count' => $failedInvitesCount,
            ],
        ]);
    }

    public function coupons(Request $request, $followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if ($followout->isReposted()) {
            return redirect()->action('API\FollowoutsController@coupons', ['followout' => $followout->getTopParentFollowout()->id]);
        }

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $coupons = new FollowoutCouponCollection(
            FollowoutCouponResource::collection(
                FollowoutCoupon::where('followout_id', $followout->id)->active()->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout_coupons' => $coupons,
            ],
        ]);
    }

    public function linkCoupon(Request $request, $followout, $coupon)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::where('author_id', $authUser->id)->find($followout);
        $coupon = Coupon::where('author_id', $authUser->id)->find($coupon);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if (is_null($coupon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Coupon not found.',
            ], 404);
        }

        if ($followout->isReposted()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($coupon->followout_coupons()->where('followout_id', $followout->id)->exists()) {
            $followoutCoupon = $coupon->followout_coupons()->where('followout_id', $followout->id)->first();
            $followoutCoupon->enableCoupon();
        } else {
            $followoutCoupon = $coupon->followout_coupons()->create([
                'followout_id' => $followout->id,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout_coupon' => new FollowoutCouponResource(FollowoutCoupon::find($followoutCoupon->id)),
            ],
        ]);
    }

    public function disableCoupon(Request $request, $followout, $coupon)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::where('author_id', $authUser->id)->find($followout);
        $coupon = Coupon::where('author_id', $authUser->id)->find($coupon);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        if (is_null($coupon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Coupon not found.',
            ], 404);
        }

        if ($followout->isReposted()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($coupon->followout_coupons()->active()->where('followout_id', $followout->id)->exists()) {
            $followoutCoupon = $coupon->followout_coupons()->where('followout_id', $followout->id)->first();

            $followoutCoupon->disableCoupon();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout coupon not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout_coupon' => new FollowoutCouponResource(FollowoutCoupon::find($followoutCoupon->id)),
            ],
        ]);
    }
}
