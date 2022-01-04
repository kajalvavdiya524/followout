<?php

namespace App\Http\Controllers\API;

use Carbon;
use Validator;
use FollowoutHelper;
use App\User;
use App\Country;
use App\Checkin;
use App\Follower;
use App\Followout;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use App\Http\Resources\FollowerResource;
use App\Http\Resources\FollowerCollection;
use App\Http\Resources\FollowoutResource;
use App\Http\Resources\FollowoutCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:admin,api')->only('updateFollowhostGoogleBusinessType');
    }

    public function me(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        // TODO: remove $withAll to improve speed
        $user = new UserResource(User::with(User::$withAll)->find($authUser->id));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    public function show(Request $request, $user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $user = new UserResource(User::with(User::$withAll)->find($user->id));

        if ($authUser && $user->blocked($authUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => $user->name.' has restricted access to '.($user->isFemale() ? 'her' : 'his').' profile.',
                'data' => [
                    'user' => $user,
                    'followouts' => [],
                ],
            ], 403);
        }

        if (($user->isPrivate() && is_null($authUser)) || ($user->isPrivate() && $authUser->id !== $user->id && !$authUser->isAdmin())) {
            return response()->json([
                'status' => 'error',
                'message' => 'This profile is private.',
                'data' => [
                    'user' => $user,
                    'followouts' => [],
                ],
            ], 403);
        }

        $followouts = $user->getOngoingOrUpcomingOrEndedRecentlyFollowouts($authUser);

        $followoutIds = $followouts->pluck('id');

        $followouts = new FollowoutCollection(
            FollowoutResource::collection(
                Followout::with(Followout::$withAll)->whereIn('_id', $followoutIds)->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'user' => $user,
                'followouts' => $followouts,
            ],
        ]);
    }

    public function updateDevice(Request $request, $user)
    {
        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        if ($authUser->id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $user->apns_device_token = $request->input('apns_device_token', null);
        $user->save();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function updateFollowhostGoogleBusinessType(Request $request, $user)
    {
        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if (!$user->isFollowhost()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not Followhost.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'google_business_type' => 'required|google_places_type',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->google_business_type = $request->input('google_business_type', null);
        $user->save();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function update(Request $request, $user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($authUser->id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if (count($request->input('removed_pictures', []))) {
            $user->deletePicturesById($request->input('removed_pictures'));
        }

        $locationRequired = $user->isFollowhost() ? 'required|' : 'nullable|';

        $pictureRequired = !$user->hasAvatar();
        $pictureRule = 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id.',_id',
            'phone_number' => 'nullable|phone_number|unique:users,phone_number,'.$user->id.',_id',
            'privacy_type' => 'nullable|in:public,private',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
            'account_categories' => 'required|array|max:5',
            'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'lat' => 'required|lat|not_in:0',
            'lng' => 'required|lng|not_in:0',
            'country_id' => $locationRequired.'exists:countries,_id',
            'city' => $locationRequired.'string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => $locationRequired.'string|max:100',
            'zip_code' => 'required|string|min:5|max:12',
            'website' => 'nullable|url',
            'education' => 'nullable|string|max:100',
            'about' => 'nullable|string|max:2500',
            'keywords' => 'nullable|string|max:140',
            'google_business_type' => 'nullable|google_places_type',
            'picture1' => $pictureRequired ? 'required_without_all:picture2,picture3|'.$pictureRule : $pictureRule,
            'picture2' => $pictureRequired ? 'required_without_all:picture1,picture3|'.$pictureRule : $pictureRule,
            'picture3' => $pictureRequired ? 'required_without_all:picture1,picture2|'.$pictureRule : $pictureRule,
            'profile_cover' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'removed_pictures' => 'nullable|array|max:3',
            'removed_pictures.*' => 'nullable|string|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($user->email !== $request->input('email')) {
            $user->email = $request->input('email');
            $user->is_activated = false;
            $user->account_activation_token = null;
            $user->save();
            $user->sendAccountActivationEmail();
        }

        $user->name = $request->input('name');
        $user->phone_number = $request->input('phone_number');
        $user->privacy_type = $request->input('privacy_type', 'public');
        $user->gender = $request->input('gender', null);
        $user->birthday = $request->input('birthday') ? Carbon::createFromFormat(config('followouts.date_format'), $request->input('birthday')) : null;
        $user->city = $request->input('city');
        $user->state = $request->input('state', null);
        $user->zip_code = $request->input('zip_code');
        $user->address = $request->input('address');
        $user->website = $request->input('website');
        $user->education = $request->input('education');
        $user->about = $request->input('about');
        $user->keywords = $request->input('keywords');

        $user->lat = doubleval($request->input('lat'));
        $user->lng = doubleval($request->input('lng'));

        if ($user->isFollowhost()) {
            $user->google_business_type = $request->input('google_business_type');
        }

        $user->country()->associate(Country::find($request->input('country_id')));

        $user->account_categories()->detach();
        $user->account_categories()->attach($request->input('account_categories'));

        $user->save();

        if ($request->hasFile('picture1')) {
            $user->saveAvatar($request->file('picture1'), 0);
        }

        if ($request->hasFile('picture2')) {
            $user->saveAvatar($request->file('picture2'), 1);
        }

        if ($request->hasFile('picture3')) {
            $user->saveAvatar($request->file('picture3'), 2);
        }

        if ($request->hasFile('profile_cover')) {
            $user->saveProfileCover($request->file('profile_cover'));
        }

        FollowoutHelper::updateDefaultFollowout($user->id);

        return response()->json([ 'status' => 'OK' ]);
    }

    public function checkins($user)
    {
        $with = ['checkins', 'checkins.followout'];

        $user = User::with($with)->find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        $checkins = $user->checkins;

        $checkins = $checkins->filter(function ($checkin, $key) use ($user, $authUser) {
            if ($authUser) {
                return $checkin->followout->isPublic() || $authUser->id === $user->id || $authUser->isAdmin();
            }

            return $checkin->followout->isPublic();
        });

        return response()->json([
            'status' => 'OK',
            'data' => [
                'checkins' => $checkins,
            ],
        ]);
    }

    public function followouts($user)
    {
        $with = [
            'followouts',
            'followouts.author',
            'followouts.author.country',
            'followouts.author.avatars',
            'followouts.author.profile_cover',
            'followouts.author.account_categories',
            'followouts.checkins',
            'followouts.checkins.user',
            'followouts.checkins.user.avatars',
            'followouts.checkins.user.profile_cover',
            'followouts.checkins.user.account_categories',
            'followouts.followees',
            'followouts.followees.user',
            'followouts.followees.user.avatars',
            'followouts.followees.user.profile_cover',
            'followouts.followees.user.account_categories',
            'followouts.followees.reward_program',
            'followouts.flyer',
            'followouts.pictures',
            'followouts.experience_categories',
        ];

        $user = User::with($with)->find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        $followouts = $user->followouts;

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        $followouts = new FollowoutCollection(FollowoutResource::collection($followouts));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followouts' => $followouts,
            ],
        ]);
    }

    public function followees(Request $request, $user)
    {
        $with = [
            'followees',
            'followees.followout',
            'followees.followout.experience_categories'
        ];

        $onlyAccepted = $request->input('only_accepted', null);

        $user = User::with($with)->find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        $followees = $user->followees;

        if ($onlyAccepted === 'true') {
            $followees = $followees->filter(function ($followee, $key) use ($user, $authUser) {
                return $followee->isAccepted();
            });
        }

        $followees = $followees->filter(function ($followee, $key) use ($user, $authUser) {
            if ($authUser) {
                return $followee->followout->isPublic() || $authUser->id === $user->id || $authUser->isAdmin();
            }

            return $followee->followout->isPublic();
        });

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followees' => $followees,
            ],
        ]);
    }

    public function following(Request $request, $user)
    {
        $user = User::with(['follows.follows', 'follows.follows.account_categories', 'follows.follows.avatars', 'follows.follows.country'])->find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        $following = $user->follows;

        return response()->json([
            'status' => 'OK',
            'data' => [
                'following' => $following,
            ],
        ]);
    }

    public function followers(Request $request, $user)
    {
        $user = User::with(['followers.follower', 'followers.follower.account_categories', 'followers.follower.avatars', 'followers.follower.country'])->find($user);

         if (is_null($user)) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'User not found.'
             ], 404);
         }

         $authUser = auth()->guard('api')->user();

         $followers = $user->followers;

         return response()->json([
             'status' => 'OK',
             'data' => [
                 'followers' => $followers,
             ],
         ]);
    }

    public function subscribers(Request $request, $user)
    {
        $with = [
            'subscriber.accepted_followees',
            'subscriber.account_categories',
            'subscriber.avatars',
            'subscriber.profile_cover',
            'subscriber.country',
            'subscriber.followouts',
            'subscriber.followouts.author',
            'subscriber.followouts.experience_categories',
            'subscriber.followouts.flyer',
            'subscriber.followouts.pictures',
            'subscriber.follows',
            'subscriber.subscribers',
            'subscriber.blocked_users',
            'subscriber.blocked_by_users',
        ];

        $user = User::with(['subscribers.subscriber'])->find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $authUser = auth()->guard('api')->user();

        $subscriberIds = $user->subscribers->pluck('id');

        $subscribers = new FollowerCollection(
            FollowerResource::collection(
                Follower::with($with)->whereIn('_id', $subscriberIds)->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'subscribers' => $subscribers,
            ],
        ]);
    }

    public function getUsers(Request $request)
    {
        $users = User::query()->activated()->with(User::$withAll)->orderBy('created_at');

        if ($request->input('email', null)) {
            $users->where('email', 'like', $request->input('email'));
        }

        $users = $users->get();
        $authUser = auth()->guard('api')->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.'
            ], 403);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'users' => $users,
            ],
        ]);
    }

    public function getClosestFollowees($latlng = null)
    {
        $authUser = auth()->guard('api')->user();

        $followees = User::activated()->followees()->with(User::$withBasic);

        if (!$authUser->isAdmin()) {
            $followees->public();
        }

        $followees = $followees->orderBy('name')->get();

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followees' => $followees,
            ],
        ]);
    }

    public function activateAccount($token)
    {
        $user = User::where('account_activation_token', $token)->first();

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token is invalid.',
            ], 403);
        }

        $user->account_activation_token = null;
        $user->is_activated = true;
        $user->save();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function resendAccountActivationEmail()
    {
        if (auth()->guard('api')->user()->isActivated()) {
            return response()->json([
                'status' => 'error',
                'message' => 'User has already activated his account.',
            ]);
        }

        auth()->guard('api')->user()->sendAccountActivationEmail();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function subscribe($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Can\'t subscribe to your own account.'
            ], 403);
        }

        if ($user->isPrivate()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Can\'t subscribe to user with a private profile.'
            ], 403);
        }

        if (!$authUser->following($user->id)) {
            $authUser->follow($user->id);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'subscribed' => true,
            ],
        ]);
    }

    public function unsubscribe($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t unsubscribe from your own account.'
            ], 403);
        }

        if ($authUser->following($user->id)) {
            $authUser->unfollow($user->id);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'subscribed' => false,
            ],
        ]);
    }

    public function requestAccountDeletion()
    {
        auth()->guard('api')->user()->markForAccountDeletion();

        return response()->json([ 'status' => 'OK' ]);
    }

    public function block(Request $request, $user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ($user->id === $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t block your own account.'
            ], 403);
        }

        if ($user->isFollowhost()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t block Followhosts.'
            ], 403);
        }

        $authUser->block($user->id);

        return response()->json([ 'status' => 'OK' ]);
    }

    public function unblock($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        $authUser->unblock($user->id);

        return response()->json([ 'status' => 'OK' ]);
    }

    public function getAvatarUrl($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'avatar_url' => $user->avatarURL(),
            ],
        ]);
    }
}
