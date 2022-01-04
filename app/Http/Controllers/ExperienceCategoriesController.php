<?php

namespace App\Http\Controllers;

use App\FollowoutCategory;
use Illuminate\Http\Request;

class ExperienceCategoriesController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $experienceCategories = FollowoutCategory::all();

        return view('admin.experience-categories.index', compact('experienceCategories'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(FollowoutCategory $category)
    {
        return view('admin.experience-categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FollowoutCategory $category)
    {
        $this->validate($request, [
            'name' => 'required|string|max:100',
        ]);

        $category->update([
            'name' => $request->input('name')
        ]);

        session()->flash('toastr.success', 'Experience category has been updated.');

        return redirect()->route('app.experience-categories.index');
    }
}
