<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function deleteUser($user)
    {
        $authUser = auth()->guard('api')->user();

        if (!$authUser->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        if ($authUser->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t delete your own account.'
            ], 404);
        }

        $user->deleteAccount();

        return response()->json([ 'status' => 'OK' ]);
    }
}
