<?php

namespace App\Http\Controllers;

use App;
use Artisan;
use PaymentHelper;
use FollowoutHelper;
use App\User;
use App\Product;
use App\StaticContent;
use App\FollowoutCategory;
use App\SalesRepresentative;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function manageUsers()
    {
        $users = User::friends()->orderBy('created_at', 'DESC')->get();

        $usersToBeDeleted = User::toBeDeleted()->orderBy('created_at', 'ASC')->get();

        return view('admin.manage-users', compact('users', 'usersToBeDeleted'));
    }

    public function editStaticContent()
    {
        $data['about'] = StaticContent::where('name', 'about')->first();
        $data['landing_hero'] = StaticContent::where('name', 'landing_hero')->first();
        $data['university'] = StaticContent::where('name', 'university')->first();
        $data['sales_rep_agreement'] = StaticContent::where('name', 'sales_rep_agreement')->first();

        return view('admin.edit-static-content', compact('data'));
    }

    public function updateAboutPage(Request $request)
    {
        $request->validate([
            'about' => 'required|string|max:100000',
            'terms' => 'required|string|max:100000',
            'privacy' => 'required|string|max:100000',
            'ach' => 'required|string|max:100000',
            'community_standards' => 'required|string|max:100000',
            'become_followee' => 'required|string|max:100000',
            'become_followhost' => 'required|string|max:100000',
        ]);

        $content = StaticContent::firstOrCreate(['name' => 'about'], ['name' => 'about']);

        $content->update($request->all());

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->back();
    }

    public function updateSalesRepAgreement(Request $request)
    {
        $request->validate([
            'agreement' => 'required|string|max:100000',
        ]);

        $content = StaticContent::firstOrCreate(['name' => 'sales_rep_agreement'], ['name' => 'sales_rep_agreement']);

        $content->update($request->all());

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->back();
    }

    public function updateLandingPage(Request $request)
    {
        $request->validate([
            'gallery_picture_1_url' => 'nullable|url',
            'gallery_picture_2_url' => 'nullable|url',
            'gallery_picture_3_url' => 'nullable|url',
            'gallery_video_url' => 'nullable|url',
            'screenshot_1_video_url' => 'nullable|url',
            'screenshot_2_video_url' => 'nullable|url',
        ]);

        $content = StaticContent::firstOrCreate(['name' => 'landing_hero'], ['name' => 'landing_hero']);

        $content->update($request->all());

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->back();
    }

    public function updateUniversityPage(Request $request)
    {
        $request->validate([
            'marketing_video_title' => 'nullable|string|max:100',
            'marketing_video_url' => 'nullable|url',
            'marketing_video_thumb_url' => 'nullable|url',
        ]);

        $content = StaticContent::firstOrCreate(['name' => 'university'], ['name' => 'university']);

        $content->update($request->all());

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->back();
    }

    public function updateUsersPage(Request $request)
    {
        $request->validate([
            'anonymous_user_avatar_url' => 'nullable|url',
        ]);

        $content = StaticContent::firstOrCreate(['name' => 'users'], ['name' => 'users']);

        $content->update($request->all());

        session()->flash('toastr.success', 'Your changes have been saved.');

        return redirect()->back();
    }

    public function giveSubscription(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));

        $subscriptionTypes = Product::subscriptions()->get()->pluck('type')->toArray();

        $this->validate($request, [
            'subscription_type' => 'required|in:'.implode(',', $subscriptionTypes),
            'subscription_count' => 'required|integer|min:1',
        ]);

        if (!$user->isFollowhost()) {
            session()->flash('toastr.error', 'User is not a Followhost.');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        if ($user->subscribed() && $user->subscription->isChargebeeSubscription()) {
            session()->flash('toastr.error', 'User is subscribed via ChargeBee.');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        $subscriptionType = $request->input('subscription_type');
        $subscriptionCount = (int) $request->input('subscription_count');

        for ($i = 1; $i <= $subscriptionCount; $i++) {
            PaymentHelper::updateOrCreateSubscription($user->id, $subscriptionType);
        }

        session()->flash('toastr.success', $user->name.' is now subscribed!');

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function removeSubscription(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));

        if (!$user->isFollowhost()) {
            session()->flash('toastr.error', 'User is not a Followhost.');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        if (!$user->subscribed()) {
            session()->flash('toastr.error', 'User is not subscribed.');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        session()->flash('toastr.success', $user->name.' subscription has been canceled.');

        if ($user->subscription->isChargebeeSubscription()) {
            session()->flash('toastr.success', $user->name.' subscription has been canceled. Don\'t forget to issue a refund via ChargeBee.');
        }

        $user->subscription->cancelAndDelete(false);

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function setSalesRepCode(Request $request, User $user)
    {
        if ($user->wasInvitedBySalesRep()) {
            return abort(403);
        }

        $request->validate([
            'code' => 'required|string|sales_rep_code_exists',
        ]);

        $salesRep = SalesRepresentative::where('code', $request->input('code'))->orWhere('promo_code', $request->input('code'))->first();

        $viaPromoCode = false;

        if ($request->input('code') === $salesRep->promo_code) {
            $viaPromoCode = true;
        }

        $salesRep->addReferredUser($user->id, $viaPromoCode);

        session()->flash('toastr.success', 'Sales representative code has been set.');

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function loginAsUser(User $user)
    {
        auth()->login($user, true);

        return redirect('/');
    }

    public function setUserRole(Request $request, User $user, $role)
    {
        if ($user->hasRole($role)) {
            session()->flash('toastr.error', 'User is already a '.ucfirst($role).'.');
            return redirect()->route('users.show', ['user' => $user->id]);
        }

        if ($role === 'friend' || $role === 'followee' || $role === 'followhost' || $role === 'admin') {
            $user->setRole($role);
        }

        session()->flash('toastr.success', 'User is now a '.ucfirst($role).'.');

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function updateOrCreateDefaultFollowout(User $user)
    {
        if ($user->isFollowhost() && $user->subscribed()) {
            FollowoutHelper::updateOrCreateDefaultFollowout($user->id);

            session()->flash('toastr.success', 'Default Followout has been updated.');
        } else {
            session()->flash('toastr.error', 'User is not authorized to have a default Followout.');
        }

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function declineAccountDeletionRequest(User $user)
    {
        $user->requested_account_deletion_at = null;
        $user->save();

        session()->flash('toastr.success', 'Account deletion request has been declined.');

        return redirect()->route('users.show', ['user' => $user->id]);
    }

    public function deleteUser(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403, 'Access denied.');
        }

        if (auth()->user()->id === $user->id) {
            return redirect()->route('me');
        }

        session()->flash('toastr.success', 'User '.$user->name.' has been deleted.');

        $user->deleteAccount();

        return redirect()->route('me');
    }

    public function confirmDeploy()
    {
        // Go to project root
        chdir(base_path());

        // Check for updates
        shell_exec("git fetch 2>&1");

        try {
            $data['output'] = file_get_contents(storage_path('/logs/deployment.log'));
        } catch (\Exception $e) {
            // Do nothing...
        }

        $data['versions'] = $this->listAllCommits();
        $data['currentVersion'] = trim(shell_exec("git log --pretty='%H' -n1 HEAD 2>&1"));
        $data['latestVersion'] = trim(shell_exec("git log --pretty='%H' -n1 origin/master 2>&1"));

        return view('admin.deploy', compact('data'));
    }

    public function deploy()
    {
        $currentVersion = trim(shell_exec("git log --pretty='%H' -n1 HEAD 2>&1"));
        $latestVersion = trim(shell_exec("git log --pretty='%H' -n1 origin/master 2>&1"));

        if (!app()->environment(['staging', 'production']) || $currentVersion === $latestVersion) {
            return redirect()->route('app.deploy');
        }

        // Go to project root
        chdir(base_path());

        // Run deploy command
        shell_exec('sh deploy.sh > ' . storage_path('/logs/deployment.log') . ' 2>&1');

        return view('admin.deployed');
    }

    public function php()
    {
        if (app()->isProduction()) {
            if (!(auth()->check() && auth()->user()->isAdmin())) {
                return abort(404);
            }
        }

        return view('php');
    }

    private function listAllCommits()
    {
        // Go to project root
        chdir(base_path());

        // Check for updates
        shell_exec("git fetch 2>&1");

        // List all commits
        $output = explode("\n", trim(shell_exec("git log origin/master --first-parent --relative-date --since=2018-01-15 --pretty=format:'%H %s (%ad)' 2>&1")));

        $versions = collect([]);

        foreach ($output as $key => $description) {
            $commit = explode(' ', $description, 2);

            $hash = $commit[0];
            $description = trim($commit[1]);

            $versions->put($hash, $description);
        }

        return $versions;
    }
}
