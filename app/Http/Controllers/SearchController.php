<?php

namespace App\Http\Controllers;

use FollowoutHelper;
use App\User;
use App\Country;
use App\Blacklist;
use App\Followout;
use App\FollowoutCategory;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function followouts(Request $request)
    {
        $query['title'] = $request->input('title', null);

        $data['ads'] = collect([]);
        $data['sponsored_followouts'] = collect([]);

        if (isset($query['title'])) {
            $data['followouts'] = Followout::ongoingOrUpcoming()->where('title', 'like', '%'.$query['title'].'%')->orderBy('name')->get();
        } else {
            $data['followouts'] = Followout::ongoingOrUpcoming()->orderBy('name')->get();
        }

        $data['followouts'] = FollowoutHelper::filterFollowoutsForUser($data['followouts'], auth()->user());

        return view('followouts.search', compact('query', 'data'));
    }

    public function users(Request $request)
    {
        $data['userTypes'] = [
            'friend' => 'Friends only',
            'followee' => 'Followees only',
            'followhost' => 'Followhosts only',
        ];

        $data['countries'] = Country::orderBy('name', 'ASC')->get();
        $data['experience_categories'] = FollowoutCategory::orderBy('name', 'ASC')->get();

        $query['name'] = $request->input('name', null);
        $query['user_type'] = $request->input('user_type', null);
        $query['experience'] = $request->input('experience', null);
        $query['country'] = $request->input('country', null);
        $query['keyword'] = $request->input('keyword', null);

        $users = User::activated();

        if (!(auth()->check() && auth()->user()->isAdmin())) {
            $users->public();
        }

        $data['users'] = collect([]);

        if (is_null($query['name']) && is_null($query['user_type']) && is_null($query['experience']) && is_null($query['country']) && is_null($query['keyword'])) {
            $data['users'] = $users->paginate(100);
        } else {
            if ($query['name']) {
                $q = clone $users;
                $q->where('name', 'like', '%' . $query['name'] . '%');

                $data['users'] = $data['users']->merge($q->get()->toBase());
            }

            if ($query['user_type']) {
                $q = clone $users;

                if ($query['user_type'] === 'friend') {
                    $q->whereIn('role', ['friend', 'admin']);
                } else {
                    $q->where('role', $query['user_type']);
                }

                $data['users'] = $data['users']->merge($q->get()->toBase());
            }

            if ($query['experience']) {
                $q = clone $users;
                $q->whereIn('followout_category_ids', [$query['experience']]);

                $data['users'] = $data['users']->merge($q->get()->toBase());
            }

            if ($query['country']) {
                $q = clone $users;
                $q->where('country_id', $query['country']);

                $data['users'] = $data['users']->merge($q->get()->toBase());
            }

            if ($query['keyword']) {
                $q = clone $users;
                $q->where('keywords', 'like', '%' . $query['keyword'] . '%');

                $data['users'] = $data['users']->merge($q->get()->toBase());
            }

            $data['users'] = $data['users']->unique();

            $data['users'] = $this->paginate($data['users'], 100, $request->input('page', null));
        }

        return view('search.users', compact('query', 'data'));
    }
}
