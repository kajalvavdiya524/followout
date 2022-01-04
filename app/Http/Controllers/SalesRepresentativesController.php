<?php

namespace App\Http\Controllers;

use Mail;
use Str;
use App\StaticContent;
use App\SalesRepresentative;
use Illuminate\Http\Request;

class SalesRepresentativesController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:admin')->except(['salesRepAgreement', 'acceptSalesRepAgreement']);
    }

    public function index()
    {
        $salesReps = SalesRepresentative::all();

        return view('admin.sales-reps.index', compact('salesReps'));
    }

    public function create()
    {
        return view('admin.sales-reps.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'nullable|string|max:128',
            'last_name' => 'nullable|string|max:128',
            'email' => 'required|unique:sales_representatives,email',
            'phone' => 'nullable|phone_number',
            'code' => 'required|string|min:3|max:250|unique:sales_representatives,code',
        ]);

        $rep = new SalesRepresentative;
        $rep->first_name = $request->input('first_name', null);
        $rep->last_name = $request->input('last_name', null);
        $rep->email = $request->input('email');
        $rep->phone = $request->input('phone', null);
        $rep->code = $request->input('code');
        $rep->promo_code = $request->input('code') . '-PROMO-' . mb_strtoupper(Str::random(10));
        $rep->hash = Str::random(32);
        $rep->accepted = false;
        $rep->save();

        Mail::send(new \App\Mail\SalesRepresentativeInvite($rep));

        session()->flash('toastr.success', 'Sales representative has been saved.');

        return redirect()->route('sales-reps.index');
    }

    public function destroy(SalesRepresentative $id)
    {
        $id->delete();

        session()->flash('toastr.success', 'Sales representative has been deleted.');

        return redirect()->route('sales-reps.index');
    }

    public function salesRepAgreement(Request $request, $hash)
    {
        $rep = SalesRepresentative::where('accepted', false)->where('hash', $hash)->first();

        if (is_null($rep)) {
            return abort(404);
        }

        $content = StaticContent::where('name', 'sales_rep_agreement')->first();

        return view('sales-rep-agreement', compact('content', 'rep'));
    }

    public function acceptSalesRepAgreement(Request $request, $hash)
    {
        $rep = SalesRepresentative::where('accepted', false)->where('hash', $hash)->first();

        if (is_null($rep)) {
            return abort(404);
        }

        $request->validate([
            'first_name' => 'required|string|max:128',
            'last_name' => 'required|string|max:128',
            'phone' => 'required|phone_number',
            'terms' => 'accepted',
        ]);

        $rep->first_name = $request->input('first_name');
        $rep->last_name = $request->input('last_name');
        $rep->phone = $request->input('phone');
        $rep->accepted = true;
        $rep->hash = null;
        $rep->save();

        Mail::send(new \App\Mail\NewSalesRepresentative($rep));

        session()->flash('toastr.success', 'Congratulations! You are now a Sales Representative of FollowOut.');

        return redirect('/');
    }
}
