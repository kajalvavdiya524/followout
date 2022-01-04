<?php

namespace App\Http\Controllers\API;

use Validator;
use SocialHelper;
use PaymentHelper;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ValidationController extends Controller
{
    public function validateUserFollows(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'follower_user_id' => 'required|exists:users,_id',
            'follows_user_id' => 'required|exists:users,_id',
        ]);

        if ($validator->fails()) {
            return response()->json([ 'status' => false ]);
        }

        $user = User::find($request->input('follower_user_id'));

        $result = $user->following($request->input('follows_user_id'));

        return response()->json([ 'status' => $result ]);
    }

    public function validateUserFavorited(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'model_name' => 'required',
            'model_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([ 'status' => false ]);
        }

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([ 'status' => false ]);
        }

        if (mb_strtolower($request->input('model_name')) === 'followout') {
            $result = $user->saved_followouts()->where('favoriteable_id', $request->input('model_id'))->exists();
        } else {
            $result = false;
        }

        return response()->json([ 'status' => $result ]);
    }

    public function validateUserExistsByEmail(Request $request)
    {
        $result = User::where('email', $request->input('email', null))->exists();

        return response()->json([ 'status' => $result ]);
    }

    public function validateUserHasSocialAccount(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:facebook',
        ]);

        if ($validator->fails()) {
            return response()->json([ 'status' => false ]);
        }

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([ 'status' => false ]);
        }

        $result = SocialHelper::providerConnected($user, $request->input('provider'));

        return response()->json([ 'status' => $result ]);
    }

    public function validatePromoCode(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $valid = PaymentHelper::validatePromoCode($request->input('promo_code', null), $authUser);

        return response()->json([ 'status' => $valid ]);
    }
}
