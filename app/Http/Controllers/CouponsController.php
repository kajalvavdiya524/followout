<?php

namespace App\Http\Controllers;

use Carbon;
use FollowoutHelper;
use App\Coupon;
use App\Product;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = auth()->user()->coupons;

        return view('coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!(auth()->user()->subscribed() || auth()->user()->isAdmin())) {
            session()->flash('toastr.error', 'Active subscription is required.');
            return redirect()->route('coupons.index');
        }

        return view('coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!(auth()->user()->subscribed() || auth()->user()->isAdmin())) {
            session()->flash('toastr.error', 'Active subscription is required.');
            return redirect()->route('coupons.index');
        }

        $discountRule = '';

        // Special case for "%" type
        if ($request->input('discount_type') == '0') {
            $discountRule .= '|between:1,100';
        }

        // Special case for "$" type
        if ($request->input('discount_type') == '1') {
            $discountRule .= '|min:0.01';
        }

        // Special case for "Offer" type
        if ($request->input('discount_type') == '2') {
            $discountRule .= '|min:0|max:0';
        }

        $this->validate($request, [
            'picture' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120|max:10000',
            'qr_code' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120,ratio=1/1|max:10000',
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:300',
            'code' => 'nullable|string|max:300',
            'promo_code' => 'nullable|string|max:300',
            'discount_type' => 'required|in:0,1,2',
            'discount' => 'required|numeric' . $discountRule,
            'expires_at' => 'required|date_format:' . config('followouts.date_format') . '|after_or_equal:-25 hours',
        ]);

        $coupon = new Coupon;
        $coupon->author()->associate(auth()->user());
        $coupon->title = $request->input('title');
        $coupon->description = $request->input('description');
        $coupon->discount = (float) number_format(abs((float) $request->input('discount')), 2);
        $coupon->discount_type = (int) $request->input('discount_type');
        $coupon->promo_code = $request->input('promo_code', null);
        $coupon->code = $request->input('code', null);
        $expiresAt = Carbon::now()->hour(23)->minute(59)->second(59)->format('h:i A').' '.$request->input('expires_at');
        $coupon->expires_at = Carbon::createFromFormat(config('followouts.datetime_format'), $expiresAt, session_tz())->tz('UTC');
        $coupon->save();

        if ($request->hasFile('picture')) {
            $coupon->savePicture($request->file('picture'));
        }

        if ($request->hasFile('qr_code')) {
            $coupon->saveQRCode($request->file('qr_code'));
        }

        session()->flash('toastr.success', 'GEO Coupon has been saved.');

        return redirect()->route('coupons.index');
    }

    public function createFollowout(Coupon $coupon)
    {
        if (!(auth()->user()->isFollowhost() && auth()->user()->subscribedToPro())) {
            return abort(403);
        }

        if ($coupon->followout) {
            session()->flash('toastr.error', 'GEO Coupon Followout already exists.');
            return redirect()->route('coupons.index');
        }

        $followout = FollowoutHelper::createFollowoutFromCoupon($coupon);

        session()->flash('toastr.success', 'GEO Coupon Followout has been created.');

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        return redirect()->route('coupons.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        return redirect()->route('coupons.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coupon $coupon)
    {
        return redirect()->route('coupons.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {
        if ($coupon->author->id !== auth()->user()->id) {
            return abort(403);
        }

        $coupon->deleteCoupon();

        session()->flash('toastr.success', 'GEO Coupon has been deleted.');

        return redirect()->route('coupons.index');
    }
}
