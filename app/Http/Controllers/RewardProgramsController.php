<?php

namespace App\Http\Controllers;

use Gate;
use Carbon;
use App\Followout;
use App\RewardProgram;
use Illuminate\Http\Request;

class RewardProgramsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewardPrograms = RewardProgram::where('author_id', auth()->user()->id)->orderByDesc('created_at')->get();

        return view('reward_programs.index', compact('rewardPrograms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (Gate::denies('manage-reward-programs')) {
            return abort(403, 'Access denied.');
        }

        if (auth()->user()->hasOpenDisputes()) {
            return abort(403, 'Please resolve open transactions first.');
        }

        $followouts = auth()->user()->followouts()->public()->notReposted()->ongoingOrUpcoming()->doesntHave('reward_programs')->get();

        return view('reward_programs.create', compact('followouts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(RewardProgram $rewardProgram)
    {
        if (Gate::denies('manage-reward-programs')) {
            return abort(403, 'Access denied.');
        }

        if ($rewardProgram->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        if (!$rewardProgram->canBeUpdated()) {
            return abort(403, 'Reward program can no longer be updated.');
        }

        return view('reward_programs.edit', compact('rewardProgram'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (Gate::denies('manage-reward-programs')) {
            return abort(403, 'Access denied.');
        }

        if (auth()->user()->hasOpenDisputes()) {
            return abort(403, 'Please resolve open transactions first.');
        }

        $followouts = auth()->user()->followouts()->public()->notReposted()->ongoingOrUpcoming()->doesntHave('reward_programs')->get();
        $followoutIds = $followouts->pluck('_id')->toArray();

        $rules = [
            'picture' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120|max:10000',
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:128',
            'redeem_count' => 'required|integer|min:1|max:1000000',
            'followout_id' => 'required|in:' . implode(',', $followoutIds),
            'enabled' => 'required|boolean',
            'redeem_code' => 'required|string|min:3|max:128',
        ];

        $request->validate($rules);

        $followout = Followout::findOrFail($request->input('followout_id'));

        $rewardProgram = new RewardProgram;
        $rewardProgram->title = $request->input('title');
        $rewardProgram->description = $request->input('description');
        $rewardProgram->redeem_count = (int) $request->input('redeem_count');
        $rewardProgram->redeem_code = $request->input('redeem_code');
        $rewardProgram->enabled = (bool) $request->input('enabled');
        $rewardProgram->require_coupon = (bool) $request->input('require_coupon');
        $rewardProgram = auth()->user()->reward_programs()->save($rewardProgram);

        $rewardProgram->author()->associate(auth()->user());
        $rewardProgram->followout()->associate($followout);
        $rewardProgram->save();

        if ($request->hasFile('picture')) {
            $rewardProgram->savePicture($request->file('picture'));
        }

        // If at least one attached coupon is required we'll disable the reward program until Followhost attaches the coupon
        if ($rewardProgram->require_coupon && $followout->coupons()->active()->count() === 0) {
            $rewardProgram->enabled = false;
            $rewardProgram->save();
        }

        session()->flash('toastr.success', 'Reward program has been created.');

        return redirect()->route('reward_programs.index');
    }

    /**
     * Update the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RewardProgram $rewardProgram)
    {
        if (Gate::denies('manage-reward-programs')) {
            return abort(403, 'Access denied.');
        }

        if ($rewardProgram->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        if (!$rewardProgram->canBeUpdated()) {
            return abort(403, 'Reward program can no longer be updated.');
        }

        $rules = [
            'picture' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120|max:10000',
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:128',
            'redeem_count' => 'required|integer|min:1|max:1000000',
            'redeem_code' => 'required|string|min:3|max:128',
            'enabled' => 'required|boolean',
            'removed_pictures' => 'nullable|array|max:1',
            'removed_pictures.*' => 'nullable|string|distinct',
        ];

        $request->validate($rules);

        $rewardProgram->title = $request->input('title');
        $rewardProgram->description = $request->input('description');
        $rewardProgram->redeem_count = (int) $request->input('redeem_count');
        $rewardProgram->redeem_code = $request->input('redeem_code');
        $rewardProgram->enabled = (bool) $request->input('enabled');
        $rewardProgram->require_coupon = (bool) $request->input('require_coupon');
        $rewardProgram->save();

        if ($request->input('removed_pictures')) {
            $rewardProgram->deletePicture();
        }

        if ($request->hasFile('picture')) {
            $rewardProgram->savePicture($request->file('picture'));
        }

        session()->flash('toastr.success', 'Reward program has been updated.');

        return redirect()->route('reward_programs.index');
    }

    /**
     * Pause the specified resource.
     *
     * @param  \App\RewardProgram  $rewardProgram
     * @return \Illuminate\Http\Response
     */
    public function pause(RewardProgram $rewardProgram)
    {
        if (auth()->user()->id !== $rewardProgram->author_id) {
            return abort(403);
        }

        $rewardProgram->enabled = false;
        $rewardProgram->save();

        session()->flash('toastr.success', 'Reward Program has been paused.');

        return redirect()->back();
    }

    /**
     * Pause the specified resource.
     *
     * @param  \App\RewardProgram  $rewardProgram
     * @return \Illuminate\Http\Response
     */
    public function resume(RewardProgram $rewardProgram)
    {
        if (auth()->user()->id !== $rewardProgram->author_id) {
            return abort(403);
        }

        // If at least one attached coupon is required we'll disable the reward program until Followhost attaches the coupon
        if ($rewardProgram->require_coupon && $rewardProgram->followout->coupons()->active()->count() === 0) {
            session()->flash('toastr.error', 'Please attach a valid coupon to followout first.');
        } else {
            $rewardProgram->enabled = true;
            $rewardProgram->save();

            session()->flash('toastr.success', 'Reward Program has been resumed.');
        }

        return redirect()->back();
    }
}
