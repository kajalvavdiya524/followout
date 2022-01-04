<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use Gate;
use Carbon;
use Storage;
use FollowoutHelper;
use App\User;
use App\Country;
use App\FollowoutCategory;
use App\Rules\NoFollowoutWordInString;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexFollowees(Request $request)
    {
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();

        $data['ads'] = collect([]);
        $data['sponsored_followouts'] = collect([]);

        $category = FollowoutCategory::find($request->input('category'));

        $data['category'] = is_null($category) ? null : $category;

        if (auth()->check() && auth()->user()->isAdmin()) {
            $data['users'] = is_null($category) ? null : User::activated()->followees()->whereIn('followout_category_ids', [ $category->id ])->orderBy('name')->get();
        } else {
            $data['users'] = is_null($category) ? null : User::activated()->public()->followees()->whereIn('followout_category_ids', [ $category->id ])->orderBy('name')->get();
        }

        return view('users.index-followees', compact('data'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexFollowhosts(Request $request)
    {
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();

        $data['ads'] = collect([]);
        $data['sponsored_followouts'] = collect([]);

        $category = FollowoutCategory::find($request->input('category'));

        $data['category'] = is_null($category) ? null : $category;

        if (auth()->check() && auth()->user()->isAdmin()) {
            $data['users'] = is_null($category) ? null : User::activated()->followhosts()->whereIn('followout_category_ids', [ $category->id ])->orderBy('name')->get();
        } else {
            $data['users'] = is_null($category) ? null : User::activated()->public()->followhosts()->whereIn('followout_category_ids', [ $category->id ])->orderBy('name')->get();
        }

        return view('users.index-followhosts', compact('data'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {         
        if (auth()->check() && $user->blocked(auth()->user()->id)) {
            return view('users.show-blocked', compact('user'));
        }

        if (!$user->isPrivate() || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->id === $user->id))) {
            $followouts = $user->getOngoingOrUpcomingOrEndedRecentlyFollowouts(auth()->user());
            $subscribers = $user->subscribers()->orderBy('created_at', 'desc')->take(100)->get();
            $subscriptions = $user->follows()->orderBy('created_at', 'desc')->get();

            return view('users.show', compact('user', 'followouts', 'subscribers', 'subscriptions'));
        }

        $followouts = $user->getOngoingOrUpcomingOrEndedRecentlyFollowouts(auth()->user());

        return view('users.show-private', compact('user', 'followouts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        if (auth()->user()->id !== $user->id) {
            return abort(403, 'Access denied.');
        }

        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();
        $data['countries'] = \App\Country::orderBy('name', 'ASC')->get();

        return view('users.edit', compact('data', 'user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        if (auth()->user()->id !== $user->id) {
            return abort(403, 'Access denied.');
        }

        if (count($request->input('removed_pictures', []))) {
            $user->deletePicturesById($request->input('removed_pictures'));
        }

        $locationRequired = $user->isFollowhost() ? 'required|' : 'nullable|';

        $pictureRequired = !$user->hasAvatar();
        $pictureRule = 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000';
        $experienceCategoriesRule = auth()->user()->isAdmin() || auth()->user()->id === config('followouts.followout_llc_user_id') ? '' : '|max:5';

        $this->validate($request, [
            'name' => ['required', 'string', 'max:128', new NoFollowoutWordInString($user)],
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id.',_id',
            'phone_number' => 'nullable|phone_number|unique:users,phone_number,'.$user->id.',_id',
            'privacy_type' => 'required|in:public,private',
            'gender' => 'nullable|in:male,female',
            'birthday' => 'nullable|date_format:'.config('followouts.date_format').'|before:-16 years|after:-100 years',
            'account_categories' => 'required|array'.$experienceCategoriesRule,
            'account_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'lat' => 'required|lat|not_in:0',
            'lng' => 'required|lng|not_in:0',
            'country_id' => $locationRequired.'exists:countries,_id',
            'state' => 'nullable|string|max:100',
            'city' => $locationRequired.'string|max:100',
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
            'removed_pictures' => 'nullable|array|max:3',
            'removed_pictures.*' => 'nullable|string|distinct',
        ]);

        if ($user->email !== $request->input('email', null)) {
            $user->email = $request->input('email');
            $user->is_activated = false;
            $user->account_activation_token = null;
            $user->save();
            $user->sendAccountActivationEmail();
        }

        $user->name = $request->input('name');
        $user->phone_number = $request->input('phone_number', null);
        $user->gender = $request->input('gender', null);
        $user->birthday = $request->input('birthday', null) ? Carbon::createFromFormat(config('followouts.date_format'), $request->input('birthday')) : null;
        $user->state = $request->input('state', null);
        $user->city = $request->input('city', null);
        $user->zip_code = $request->input('zip_code');
        $user->address = $request->input('address', null);
        $user->website = $request->input('website', null);
        $user->education = $request->input('education', null);
        $user->about = $request->input('about');
        $user->keywords = $request->input('keywords');

        // Friends can't have public profiles
        if ($request->input('privacy_type') === 'public' && Gate::denies('set-profile-privacy-type-public')) {
            $user->privacy_type = 'private';
        } else {
            $user->privacy_type = $request->input('privacy_type');
        }

        $user->lat = doubleval($request->input('lat'));
        $user->lng = doubleval($request->input('lng'));

        if ($user->isFollowhost()) {
            $user->google_business_type = $request->input('google_business_type');
        }

        $user->country()->associate(Country::find($request->input('country_id')));

        $user->account_categories()->detach();
        $user->account_categories()->attach($request->input('account_categories'));
        if($request->input('video_url') != ''){ 
            $user->video_url = $request->input('video_url');
        }else{
            $user->video_url = "";
        }
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

        FollowoutHelper::updateDefaultFollowout($user->id);

        session()->flash('toastr.success', 'Your profile has been updated.');

        return redirect()->route('users.show', [ 'user' => $user->id ]);
    }

    public function me()
    {
        session()->reflash();

        return redirect()->route('users.show', ['user' => auth()->user()->id]);
    }

    public function askForAccountActivation()
    {
        if (auth()->user()->isActivated()) {
            return redirect('/');
        }

        return view('activate-account');
    }

    public function resendAccountActivationEmail()
    {
        if (!auth()->user()->isActivated()) {
            auth()->user()->sendAccountActivationEmail();
            session()->flash('toastr.success', 'Account activation email has been sent.');
        }

        return redirect('/');
    }

    public function activateAccount($token)
    {
        $user = User::where('account_activation_token', $token)->first();

        if (is_null($user)) {
            session()->flash('toastr.error', 'Token is invalid.');
            return redirect('/');
        }

        $user->account_activation_token = null;
        $user->is_activated = true;
        $user->save();

        session()->flash('toastr.success', 'Your account has been activated.');

        return redirect('/');
    }

    public function subscribe(User $user)
    {
        if ($user->id === auth()->user()->id) {
            session()->flash('toastr.error', 'Can\'t subscribe to your own account.');
            return redirect()->back();
        }

        if ($user->isPrivate()) {
            session()->flash('toastr.success', 'Can\'t subscribe to user with a private profile.');
        }

        if (auth()->user()->following($user->id)) {
            session()->flash('toastr.success', 'You are already subscribed to '.$user->name.'.');
        } else {
            auth()->user()->follow($user->id);
            session()->flash('toastr.success', 'You are now subscribed to '.$user->name.'.');
        }

        return redirect()->back();
    }

    public function unsubscribe(User $user)
    {
        if (auth()->user()->following($user->id)) {
            auth()->user()->unfollow($user->id);
            session()->flash('toastr.success', 'You are no longer subscribed to '.$user->name.'.');
        }

        return redirect()->back();
    }

    public function accountDeletionConfirmation()
    {
        return view('settings.delete-account');
    }

    public function requestAccountDeletion()
    {
        auth()->user()->markForAccountDeletion();

        session()->flash('toastr.success', 'Your account deletion request has been sent.');

        return redirect()->route('settings.account');
    }

    public function getAvatarFile(User $user)
    {
        if (!$user->hasAvatar()) {
            $url = $user->avatarURL();

            return response()->streamDownload(function () use ($url) {
                echo file_get_contents($url);
            }, basename($url));
        }

        $avatar = $user->avatars()->orderBy('created_at')->first();

        return Storage::download($avatar->path, basename($avatar->path));
    }    
}
