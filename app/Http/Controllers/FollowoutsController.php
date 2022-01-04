<?php

namespace App\Http\Controllers;

use Carbon;
use FollowoutHelper;
use Gate;
use Mail;
use Str;
use App\User;
use App\Coupon;
use App\Product;
use App\Country;
use App\Followee;
use App\Followout;
use App\FollowoutCoupon;
use App\FollowoutCategory;
use Illuminate\Http\Request;

class FollowoutsController extends Controller
{
    public function index(Request $request)
    {
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();

        $data['ads'] = collect([]);
        $data['sponsored_followouts'] = collect([]);

        $category = FollowoutCategory::find($request->input('category'));

        $data['category'] = $category;

        $data['followouts'] = is_null($category) ? null : Followout::ongoingOrUpcoming()->whereIn('followout_category_ids', [$category->id])->orWhere('followout_category_ids', $category->id)->orderBy('name')->get();

        $data['followouts'] = FollowoutHelper::filterFollowoutsForUser($data['followouts'], auth()->user());

        return view('followouts.index', compact('data'));
    }

    public function create(Request $request)
    {
        $data = FollowoutHelper::getEmptyFollowoutTemplateData(auth()->user()->getKey());

        // Merge data into request and redirect to actual save route
        $request = $request->merge($data);

        return $this->store($request);
    }

    public function createManually(Request $request, $followhost = null)
    {
        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();
        $data['countries'] = Country::orderBy('name', 'ASC')->get();

        if (is_null($followhost) && auth()->user()->isFollowhost()) {
            return redirect()->route('followouts.create-manually', ['followhost' => auth()->user()->id]);
        }

        if ($followhost) {
            $data['followhost'] = User::find($followhost);
            if ($data['followhost']->id !== auth()->user()->id) {
                $data['followhost'] = $data['followhost'] && $data['followhost']->isFollowhost() && $data['followhost']->subscribed() ? $data['followhost'] : null;
            }
        }

        return view('followouts.create', compact('data'));
    }

    public function store(Request $request)
    {
        $virtualAddressRule = $request->input('is_virtual', null) ? 'required_with:is_virtual|url' : 'required_with:is_virtual';
        $experienceCategoriesRule = auth()->user()->isAdmin() || auth()->user()->id === config('followouts.followout_llc_user_id') ? '' : '|max:5';

        $rules = [
            'followhost' => 'nullable|exists:users,_id',
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:2500',
            'experience_categories' => 'required|array' . $experienceCategoriesRule,
            'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
            'is_virtual' => 'nullable',
            'virtual_address' => $virtualAddressRule,
            'city' => 'required_without:is_virtual|max:100',
            'state' => 'nullable|max:100',
            'address' => 'required_without:is_virtual|max:100',
            'zip_code' => 'required_without:is_virtual|max:12',
            'lat' => 'required_without:is_virtual|lat|not_in:0',
            'lng' => 'required_without:is_virtual|lng|not_in:0',
            'radius' => 'nullable|integer|min:1|max:10000',
            'starts_at_time' => 'required|date_format:' . config('followouts.time_format'),
            'starts_at_date' => 'required|date_format:' . config('followouts.date_format') . '|after_or_equal:-1 year|before_or_equal:+90 days|before_or_equal:ends_at_date',
            'ends_at_time' => 'required|date_format:' . config('followouts.time_format'),
            'ends_at_date' => 'required|date_format:' . config('followouts.date_format') . '|after_or_equal:starts_at_date|before:+1 year',
            'tickets_url' => 'nullable|url',
            'external_info_url' => 'nullable|url',
            'privacy_type' => 'required|in:public,private,followers',
            'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
            'picture1' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'picture2' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
            'picture3' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
        ];

        $request->validate($rules);

        $followout = new Followout;
        $followout->hash = hash('sha256', auth()->user()->id . time());
        $followout->title = $request->input('title');
        $followout->description = $request->input('description');
        $followout->tickets_url = $request->input('tickets_url', null);
        $followout->external_info_url = $request->input('external_info_url', null);
        $startsAt = $request->input('starts_at_time').' '.$request->input('starts_at_date');
        $endsAt = $request->input('ends_at_time').' '.$request->input('ends_at_date');
        $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $startsAt, session_tz())->tz('UTC');
        $followout->ends_at = Carbon::createFromFormat(config('followouts.datetime_format'), $endsAt, session_tz())->tz('UTC');

        if ($request->input('is_virtual', null)) {
            $followout->is_virtual = true;
            $followout->virtual_address = $request->input('virtual_address');

            // This would allow mobile app to find the followouts via geolocation search
            if (auth()->user()->hasLocation()) {
                $followout->lat = doubleval(auth()->user()->lat);
                $followout->lng = doubleval(auth()->user()->lng);

                if (auth()->user()->hasAddress()) {
                    $followout->city = auth()->user()->city;
                    $followout->state = auth()->user()->state;
                    $followout->address = auth()->user()->address;
                    $followout->zip_code = auth()->user()->zip_code;
                }
            } else {
                // Use default geo address, this would allow mobile app to find the followouts via geolocation search
                $followout->city = 'İskilip';
                $followout->state = 'Çorum';
                $followout->address = 'Beyoğlan';
                $followout->zip_code = '19400';
                $followout->lat = doubleval(40.866667);
                $followout->lng = doubleval(34.566667);
                $followout->geohash = 'sz0yew3q8c1';
            }

            $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        } else {
            $followout->is_virtual = false;
            $followout->city = $request->input('city');
            $followout->state = $request->input('state');
            $followout->address = $request->input('address');
            $followout->zip_code = $request->input('zip_code');
            $followout->lat = doubleval($request->input('lat'));
            $followout->lng = doubleval($request->input('lng'));
            $followout->radius = $request->input('radius');
            $followout->geohash = $request->input('geohash');
            $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        }

        if ($followout->starts_at > $followout->ends_at) {
            $followout->ends_at = $followout->starts_at->addHour();
            session()->flash('toastr.warning', 'Followout end date was incorrect and has been changed, please reset it.');
         } else if ($followout->starts_at->timestamp === $followout->ends_at->timestamp) {
             $utcOffset = now()->tz(session_tz())->utcOffset();

             $followout->starts_at = $followout->starts_at->utcOffset($utcOffset)->setTime(0, 0, 0);
             $followout->ends_at = $followout->ends_at->utcOffset($utcOffset)->setTime(23, 59, 59);

             session()->flash('toastr.warning', 'Followout start and end dates were the same and have been changed, please double check them.');
         }

        if (auth()->user()->isFollowhost() && auth()->user()->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public')) {
            FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly(auth()->user()->id);
        }

        if ($request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->denies('set-followout-privacy-type-public')) {
            $followout->privacy_type = 'followers';
        } else {
            $followout->privacy_type = $request->input('privacy_type');
        }

        $followout = auth()->user()->followouts()->save($followout);
        $followout->experience_categories()->attach($request->input('experience_categories'));
        $followout->author()->associate(auth()->user());
        $followout->save();

        if ($request->input('followhost', null) || auth()->user()->isFollowhost()) {
            if ($request->input('followhost')) {
                $followhost = User::find($request->input('followhost', null));
            } else if (auth()->user()->isFollowhost()) {
                $followhost = auth()->user();
            } else {
                $followhost = null;
            }

            if ($followhost && $followhost->isFollowhost()) {
                if ($followhost->id !== auth()->user()->id) {
                    $followout->title = $followhost->name.' Followout';
                }
                $followout->based_on_followhost()->associate($followhost);
                $followout->lat = doubleval($followhost->lat);
                $followout->lng = doubleval($followhost->lng);
                $followout->save();
            }
        }

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

        if (!auth()->user()->isFollowhost()) {
            $followee = new Followee(['status' => 'accepted']);
            $followee = $followout->followees()->save($followee);
            $followee->user()->associate(auth()->user());
            $followee->save();
        }

        if ($followout->isVirtual()) {
            $checkin = $followout->checkins()->create(['status' => 'exit']);
            $checkin->user()->associate($followout->author);
            $checkin->save();
        }

        FollowoutHelper::makeDefaultFollowoutPublicIfPossible($followout->author->id);

        Mail::to(auth()->user())->send(new \App\Mail\YourFollowoutCreated);

        if (!$followout->isDefault() && !$followout->hasFlyer() && !auth()->user()->hasDefaultFlyer()) {
            $followout->saveLocationFlyer();
        }

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function show(Request $request, $followout)
    {
        $followout = Followout::with(Followout::$withAll)->where('_id', $followout)->firstOrFail();

        $user = auth()->user();

        if (!$followout->userHasAccess($user, $request->input('hash', null))) {
            return abort(404);
        }

        $followout->incrementViews(1);

        $pendingFollowees = $followout->pending_followees()->notRequestedByUser()->get();
        $pendingApprovalFollowees = $followout->pending_followees()->requestedByUser()->get();

        return view('followouts.show', compact('followout', 'pendingFollowees', 'pendingApprovalFollowees'));
    }

    public function edit(Followout $followout)
    {
        if (!(auth()->user()->isAdmin() || auth()->user()->id === $followout->author->id)) {
            return abort(403, 'Access denied.');
        }

        $data['followout_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();
        $data['countries'] = Country::orderBy('name', 'ASC')->get();

        if ($followout->onlyPrivacyIsEditable(auth()->user())) {
            return view('followouts.edit-privacy', compact('followout', 'data'));
        }

        if ($followout->isReposted()) {
            return view('followouts.edit-reposted', compact('followout', 'data'));
        }

        if ($followout->isDefault()) {
            return view('followouts.edit-default', compact('followout', 'data'));
        }

        return view('followouts.edit', compact('followout', 'data'));
    }

    public function update(Request $request, Followout $followout)
    {
        if (!(auth()->user()->isAdmin() || auth()->user()->id === $followout->author->id)) {
            return abort(403, 'Access denied.');
        }

        $privacyTypeRule = $followout->reward_programs()->count() > 0 ? 'in:public,followers' : 'in:public,private,followers';

        if ($followout->onlyPrivacyIsEditable(auth()->user())) {
            $rules = [
                'privacy_type' => 'required|' . $privacyTypeRule,
            ];
        } else if ($followout->isReposted()) {
            $rules = [
                'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
                'removed_flyer' => 'nullable|array|max:1',
                'removed_flyer.*' => 'nullable|string|distinct',
            ];
        } else if ($followout->isDefault()) {
            $experienceCategoriesRule = auth()->user()->isAdmin() || auth()->user()->id === config('followouts.followout_llc_user_id') ? '' : '|max:5';

            $rules = [
                'description' => 'required|string|max:2500',
                'experience_categories' => 'required|array'.$experienceCategoriesRule,
                'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
                'tickets_url' => 'nullable|url',
                'external_info_url' => 'nullable|url',
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
            $experienceCategoriesRule = auth()->user()->isAdmin() || auth()->user()->id === config('followouts.followout_llc_user_id') ? '' : '|max:5';
            $virtualAddressRule = $request->input('is_virtual', null) ? 'required_with:is_virtual|url' : 'required_with:is_virtual';

            $rules = [
                'title' => 'required|string|max:128',
                'description' => 'required|string|max:2500',
                'experience_categories' => 'required|array' . $experienceCategoriesRule,
                'experience_categories.*' => 'required|string|distinct|exists:followout_categories,_id',
                'is_virtual' => 'nullable',
                'virtual_address' => $virtualAddressRule,
                'city' => 'required_without:is_virtual|max:100',
                'state' => 'nullable|max:100',
                'address' => 'required_without:is_virtual|max:100',
                'zip_code' => 'required_without:is_virtual|max:12',
                'lat' => 'required_without:is_virtual|lat|not_in:0',
                'lng' => 'required_without:is_virtual|lng|not_in:0',
                'radius' => 'nullable|integer|min:1|max:10000',
                'starts_at_time' => 'required|date_format:' . config('followouts.time_format'),
                'starts_at_date' => 'required|date_format:' . config('followouts.date_format') . '|after_or_equal:-1 year|before_or_equal:+90 days|before_or_equal:ends_at_date',
                'ends_at_time' => 'required|date_format:' . config('followouts.time_format'),
                'ends_at_date' => 'required|date_format:' . config('followouts.date_format') . '|after_or_equal:starts_at_date|before:+1 year',
                'tickets_url' => 'nullable|url',
                'external_info_url' => 'nullable|url',
                'privacy_type' => 'required|' . $privacyTypeRule,
                'flyer' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/x-m4v|max:100000',
                'picture1' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture2' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'picture3' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=150,min_height=150,max_width=5120,max_height=5120|max:10000',
                'removed_flyer' => 'nullable|array|max:1',
                'removed_flyer.*' => 'nullable|string|distinct',
                'removed_pictures' => 'nullable|array|max:3',
                'removed_pictures.*' => 'nullable|string|distinct',
            ];
        }

        $request->validate($rules);

        if ($followout->onlyPrivacyIsEditable(auth()->user())) {
            if ($followout->isGeoCoupon()) {
                if (Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                    FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly(auth()->user()->id);
                    $followout->privacy_type = 'public';
                } else {
                    $followout->privacy_type = 'followers';
                }
            } else if ($request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->denies('set-followout-privacy-type-public')) {
                $followout->privacy_type = 'followers';
            } else {
                $followout->privacy_type = $request->input('privacy_type');
            }
        } else if (!$followout->isReposted() && !$followout->isDefault()) {
            $followout->title = $request->input('title');
            $followout->description = $request->input('description');
            $followout->tickets_url = $request->input('tickets_url', null);
            $followout->external_info_url = $request->input('external_info_url', null);
            $startsAt = $request->input('starts_at_time').' '.$request->input('starts_at_date');
            $endsAt = $request->input('ends_at_time').' '.$request->input('ends_at_date');
            $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $startsAt, session_tz())->tz('UTC');
            $followout->ends_at = Carbon::createFromFormat(config('followouts.datetime_format'), $endsAt, session_tz())->tz('UTC');

            if ($request->input('is_virtual', null)) {
                $followout->is_virtual = true;
                $followout->virtual_address = $request->input('virtual_address');

                // This would allow mobile app to find the followouts via geolocation search
                if (auth()->user()->hasLocation()) {
                    $followout->lat = doubleval(auth()->user()->lat);
                    $followout->lng = doubleval(auth()->user()->lng);

                    if (auth()->user()->hasAddress()) {
                        $followout->city = auth()->user()->city;
                        $followout->state = auth()->user()->state;
                        $followout->address = auth()->user()->address;
                        $followout->zip_code = auth()->user()->zip_code;
                    }
                } else {
                    // Use default geo address, this would allow mobile app to find the followouts via geolocation search
                    $followout->city = 'İskilip';
                    $followout->state = 'Çorum';
                    $followout->address = 'Beyoğlan';
                    $followout->zip_code = '19400';
                    $followout->lat = doubleval(40.866667);
                    $followout->lng = doubleval(34.566667);
                    $followout->geohash = 'sz0yew3q8c1';
                }
            } else {
                $followout->is_virtual = false;
                $followout->city = $request->input('city');
                $followout->state = $request->input('state', null);
                $followout->address = $request->input('address');
                $followout->zip_code = $request->input('zip_code');
                $followout->lat = doubleval($request->input('lat'));
                $followout->lng = doubleval($request->input('lng'));
                $followout->radius = $request->input('radius', null);
                $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
            }

            if ($followout->starts_at > $followout->ends_at) {
                $followout->ends_at = $followout->starts_at->addHour();
                session()->flash('toastr.warning', 'Followout end date was incorrect and has been changed, please reset it.');
            } else if ($followout->starts_at->timestamp === $followout->ends_at->timestamp) {
                $followout->starts_at = $followout->starts_at->setTime(0, 0, 0);
                $followout->ends_at = $followout->ends_at->setTime(23, 59, 59);
                session()->flash('toastr.warning', 'Followout start and end dates were the same and have been changed, please double check them.');
            }

            if (auth()->user()->isFollowhost() && auth()->user()->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public')) {
                FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly(auth()->user()->id);
            }

            if ($followout->isGeoCoupon()) {
                if (Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                    FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly(auth()->user()->id);
                    $followout->privacy_type = 'public';
                } else {
                    $followout->privacy_type = 'followers';
                }
            } else if ($request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->denies('set-followout-privacy-type-public')) {
                $followout->privacy_type = 'followers';
            } else {
                $followout->privacy_type = $request->input('privacy_type');
            }

            $followout->experience_categories()->detach();
            $followout->experience_categories()->attach($request->input('experience_categories'));

            $followout->save();
        } else if ($followout->isOngoing() || $followout->isDefault()) {
            if (auth()->user()->isFollowhost() && auth()->user()->hasOngoingOrUpcomingPublicFollowout() && $request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public')) {
                FollowoutHelper::makeOngoingOrUpcomingPublicFollowoutsFollowersOnly(auth()->user()->id);
            }

            if ($followout->isGeoCoupon()) {
                if (Gate::forUser(auth()->user())->allows('set-followout-privacy-type-public') && $request->input('privacy_type') === 'public') {
                    FollowoutHelper::makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly(auth()->user()->id);
                    $followout->privacy_type = 'public';
                } else {
                    $followout->privacy_type = 'followers';
                }
            } else if ($request->input('privacy_type') === 'public' && Gate::forUser(auth()->user())->denies('set-followout-privacy-type-public')) {
                $followout->privacy_type = 'followers';
            } else {
                $followout->privacy_type = $request->input('privacy_type');
            }

            $followout->description = $request->input('description');
            $followout->tickets_url = $request->input('tickets_url', null);
            $followout->external_info_url = $request->input('external_info_url', null);

            $followout->experience_categories()->detach();
            $followout->experience_categories()->attach($request->input('experience_categories'));

            $followout->save();
        }

        if ($followout->based_on_followhost) {
            $followhost = $followout->based_on_followhost;

            if ($followhost && $followhost->isFollowhost()) {
                if ($followhost->id !== auth()->user()->id) {
                    $followout->title = $followhost->name . ' Followout';
                }
                $followout->lat = doubleval($followhost->lat);
                $followout->lng = doubleval($followhost->lng);
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

        FollowoutHelper::makeDefaultFollowoutPublicIfPossible($followout->author->id);
        FollowoutHelper::syncAttributesForRepostedFollowouts($followout);

        if (!$followout->isDefault() && !$followout->hasFlyer() && !auth()->user()->hasDefaultFlyer()) {
            $followout->saveLocationFlyer();
        }

        session()->flash('toastr.success', 'Followout has been updated.');

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function destroy(Followout $followout)
    {
        if ($followout->isDefault()) {
            return abort(403, 'Default Followout can\'t be deleted.');
        }

        if ($followout->isReposted()) {
            if (!(auth()->user()->isAdmin() ||
                  $followout->author->id === auth()->user()->id ||
                  $followout->getTopParentFollowout()->author->id === auth()->user()->id
            )) {
                return abort(403, 'Access denied.');
            }
        } else {
            if (!(auth()->user()->isAdmin() || $followout->author->id === auth()->user()->id)) {
                return abort(403, 'Access denied.');
            }
        }

        $followout->deleteFollowout();

        session()->flash('toastr.success', 'Followout has been deleted.');

        return redirect()->route('me');
    }

    public function stats(Followout $followout)
    {
        if ($followout->isReposted()) {
            if (!(auth()->user()->isAdmin() ||
                  auth()->user()->id === $followout->author->id ||
                  $followout->parent_followout->author->id === auth()->user()->id ||
                  auth()->user()->id === $followout->getTopParentFollowout()->author->id
            )) {
                return abort(403, 'Access denied.');
            }
        } else {
            if (!(auth()->user()->isAdmin() || auth()->user()->id === $followout->author->id)) {
                return abort(403, 'Access denied.');
            }
        }

        return view('followouts.stats.index', compact('followout'));
    }

    public function goToVirtualAddress(Request $request, Followout $followout)
    {
        $user = auth()->user();

        if (!$followout->userHasAccess($user, $request->input('hash', null))) {
            return abort(404);
        }

        if ($user && !$followout->hasCheckin($user->id, 'exit')) {
            $checkin = $followout->checkins()->create(['status' => 'exit']);
            $checkin->user()->associate($user);
            $checkin->save();

            $followout->author->notify(new \App\Notifications\NewCheckin($followout));

            FollowoutHelper::handleNewCheckin($followout);
        }

        return redirect($followout->virtual_address);
    }

    public function inviteFriends(Request $request, Followout $followout)
    {
        if (auth()->user()->id !== $followout->author->id) {
            return abort(403, 'Access denied.');
        }

        $request->validate([
            'invites' => 'nullable|array',
            'invites.*' => 'nullable',
        ]);

        // Remove duplicate emails
        $emails = collect(array_unique($request->input('invites', [])));

        // Fix invalid data
        $emails = $emails->map(function ($email, $key) {
            return mb_strtolower(trim($email));
        });

        // Remove invalid emails
        $emails = $emails->filter(function ($value, $key) {
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        });

        $invitedCount = 0;

        foreach ($emails as $email) {
            $invited = $followout->inviteAttendee($email, 'email');

            if ($invited) {
                $invitedCount++;
            }
        }

        session()->flash('toastr.success', $invitedCount . ' ' . Str::plural('invite', $invitedCount) . ' have been sent.');

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function inviteAttendee(Request $request, Followout $followout)
    {
        $request->validate([
            'user_id' => 'required|exists:users,_id',
            'followout_id' => 'required|exists:followouts,_id',
        ]);

        $user = User::find($request->input('user_id'));
        $followout = Followout::find($request->input('followout_id'));

        if ($followout->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        if ($followout->userHasAttended($user->id)) {
            session()->flash('toastr.error', 'You have already invited this user.');
            return redirect()->back();
        }

        $invited = $followout->inviteAttendee($user, 'user');

        if ($invited) {
            session()->flash('toastr.success', 'Your invitation has been sent.');
        } else {
            session()->flash('toastr.error', 'Can\'t invite this user.');
        }

        return redirect()->back();
    }

    public function manageCoupons(Followout $followout)
    {
        if (!(auth()->user()->isAdmin() || auth()->user()->id === $followout->author->id)) {
            return abort(403, 'Access denied.');
        }

        if ($followout->isReposted()) {
            return abort(403, 'Access denied.');
        }

        if (auth()->user()->isFollowhost() && !auth()->user()->subscribed()) {
            $yearlySubscription = Product::subscriptionYearly()->first();

            return redirect()->route('cart.add', ['product' => $yearlySubscription->id]);
        }

        $coupons = auth()->user()->coupons()->whereHas('followout_coupons', function ($query) use ($followout) {
            $query->where('followout_id', $followout->id)->active();
        })->get();

        $usedCoupons = collect([]);

        $coupons->each(function ($coupon, $key) use ($usedCoupons, $followout) {
            $usedCoupons->push($coupon->followout_coupons()->where('followout_id', $followout->id)->first());
        });

        $unusedCoupons = auth()->user()->coupons()->whereHas('followout_coupons', function ($query) use ($followout) {
            $query->where('followout_id', $followout->id)->inactive();
        })->get();

        $otherCoupons = auth()->user()->coupons()->active()->whereDoesntHave('followout_coupons', function ($query) use ($followout) {
            $query->where('followout_id', $followout->id);
        })->get();

        return view('followouts.coupons.edit', compact('followout', 'usedCoupons', 'unusedCoupons', 'otherCoupons'));
    }

    public function useCoupon(Request $request, Followout $followout, Coupon $coupon)
    {
        if (auth()->user()->id !== $followout->author->id || auth()->user()->id !== $coupon->author->id || $followout->isReposted()) {
            return abort(403, 'Access denied.');
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

        session()->flash('toastr.success', 'GEO Coupon is now used.');

        return redirect()->route('followouts.coupons.edit', ['followout' => $followout->id]);
    }

    public function disableCoupon(Request $request, Followout $followout, Coupon $coupon)
    {
        if (auth()->user()->id !== $followout->author->id || auth()->user()->id !== $coupon->author->id || $followout->isReposted()) {
            return abort(403, 'Access denied.');
        }

        if ($coupon->followout_coupons()->active()->where('followout_id', $followout->id)->exists()) {
            $followoutCoupon = $coupon->followout_coupons()->where('followout_id', $followout->id)->first();

            $followoutCoupon->disableCoupon();
        }

        session()->flash('toastr.success', 'GEO Coupon in no longer used.');

        return redirect()->route('followouts.coupons.edit', ['followout' => $followout->id]);
    }

    public function enable(Request $request, Followout $followout)
    {
        if (!(auth()->user()->id === $followout->author->id && $followout->isDefault())) {
            return abort(403, 'Access denied.');
        }

        FollowoutHelper::showDefaultFollowout(auth()->user()->id);

        session()->flash('toastr.success', 'Default followout is now visible.');

        return redirect()->back();
    }

    public function disable(Request $request, Followout $followout)
    {
        if (!(auth()->user()->id === $followout->author->id && $followout->isDefault())) {
            return abort(403, 'Access denied.');
        }

        FollowoutHelper::hideDefaultFollowout(auth()->user()->id);

        session()->flash('toastr.success', 'Default followout is now hidden.');

        return redirect()->back();
    }

    public function previewGeoCouponFollowout(Coupon $coupon)
    {
        if ($coupon->followout) {
            session()->flash('toastr.error', 'GEO Coupon Followout already exists.');
            return redirect()->route('coupons.index');
        }

        $followout = FollowoutHelper::getPreviewDataFromCoupon($coupon);

        return view('followouts.preview', compact('followout'));
    }
}
