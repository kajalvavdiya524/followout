<?php

namespace App\Http\Controllers\API;

use Carbon;
use FollowoutHelper;
use Validator;
use App\Coupon;
use App\Followout;
use App\FollowoutCoupon;
use App\UsedCoupon;
use App\Http\Resources\CouponCollection;
use App\Http\Resources\CouponResource;
use App\Http\Resources\FollowoutCouponResource;
use App\Http\Resources\FollowoutCouponCollection;
use App\Http\Resources\FollowoutResource;
use App\Http\Resources\UsedCouponResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $authUser = auth()->guard('api')->user();

        $coupons = new CouponCollection(
            CouponResource::collection(
                Coupon::with('picture', 'qr_code')->where('author_id', $authUser->getKey())->orderByDesc('created_at')->get()
            )
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'coupons' => $coupons,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        if (!($authUser->subscribed() || $authUser->isAdmin())) {
            return response()->json([
                'status' => 'error',
                'message' => 'Active subscription is required.',
            ], 403);
        }

        $discountRule = '';

        // Special case for "%" type
        if ($request->input('discount_type') == '0') {
            $discountRule .= '|between:0.01,100.00';
        }

        // Special case for "$" type
        if ($request->input('discount_type') == '1') {
            $discountRule .= '|min:0.01';
        }

        // Special case for "Offer" type
        if ($request->input('discount_type') == '2') {
            $discountRule .= '|min:0|max:0';
        }

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $coupon = new Coupon;
        $coupon->author()->associate($authUser);
        $coupon->title = $request->input('title');
        $coupon->description = $request->input('description');
        $coupon->discount = (float) number_format(abs((float) $request->input('discount')), 2);
        $coupon->discount_type = (int) $request->input('discount_type');
        $coupon->promo_code = $request->input('promo_code', null);
        $coupon->code = $request->input('code', null);
        $expiresAt = Carbon::now()->hour(23)->minute(59)->second(59)->format('h:i A') . ' ' . $request->input('expires_at');
        $coupon->expires_at = Carbon::createFromFormat(config('followouts.datetime_format'), $expiresAt)->tz('UTC');
        $coupon->save();

        if ($request->hasFile('picture')) {
            $coupon->savePicture($request->file('picture'));
        }

        if ($request->hasFile('qr_code')) {
            $coupon->saveQRCode($request->file('qr_code'));
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'coupon' => new CouponResource(Coupon::with('picture', 'qr_code')->find($coupon->id)),
            ],
        ]);
    }

    public function show(Request $request, $coupon)
    {
        $authUser = auth()->guard('api')->user();

        $coupon = FollowoutCoupon::find($coupon);

        if (is_null($coupon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout Coupon not found.',
            ], 404);
        }

        if (!$coupon->followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout_coupon' => new FollowoutCouponResource(FollowoutCoupon::find($coupon->id)),
            ],
        ]);
    }

    public function createFollowout(Request $request, $coupon)
    {
        $authUser = auth()->guard('api')->user();

        if (!$authUser->isFollowhost()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be a Followhost.',
            ], 403);
        }

        if (!$authUser->subscribedToPro()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pro subscription is required.',
            ], 403);
        }

        $coupon = Coupon::find($coupon);

        if (is_null($coupon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout Coupon not found.',
            ], 404);
        }

        if ($coupon->followout) {
            return response()->json([
                'status' => 'error',
                'message' => 'GEO Coupon Followout already exists.',
            ], 403);
        }

        $followout = FollowoutHelper::createFollowoutFromCoupon($coupon);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followout' => new FollowoutResource(Followout::with(Followout::$withAll)->find($followout->getKey())),
            ],
        ]);
    }

    public function useCoupon(Request $request, $coupon)
    {
        $authUser = auth()->guard('api')->user();

        $coupon = FollowoutCoupon::find($coupon);

        if (is_null($coupon)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Coupon not found.',
            ], 404);
        }

        if (!$coupon->followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if (!$coupon->canBeUsed($authUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Coupon cannot be used.',
            ], 403);
        }

        $usedCoupon = $coupon->use($authUser->id);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'used_coupon' => new UsedCouponResource(UsedCoupon::with('followout_coupon')->find($usedCoupon->id)),
            ],
        ]);
    }
}
