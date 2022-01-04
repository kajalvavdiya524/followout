<?php

namespace App\Http\Controllers\API;

use App\Favorite;
use App\Followout;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FavoritesController extends Controller
{
    public function index()
    {
        $authUser = auth()->guard('api')->user();

        $favorites = $authUser->favorites()->get();

        $favorites = $favorites->each(function ($favorite, $key) {
            if ($favorite->isFollowout()) {
                $favorite = $favorite->favoriteable->load(Followout::$withAll);
            }
        });

        $favorites = $favorites->filter(function ($favorite, $key) {
            if ($favorite->isFollowout()) {
                return $favorite->favoriteable->isUpcomingOrOngoing();
            }

            return true;
        });

        $favorites = $favorites->sortBy(function ($favorite, $key) {
            return $favorite->favoriteable->created_at->timestamp;
        });

        return response()->json([
            'status' => 'OK',
            'data' => [
                'favorites' => $favorites,
            ],
        ]);
    }

    public function favorite($modelName, $modelId)
    {
        $authUser = auth()->guard('api')->user();

        if (mb_strtolower($modelName) === 'followout') {
            $model = Followout::find($modelId);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Model doesn\'t exist.',
            ], 404);
        }

        if (is_null($model)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Model not found.',
            ], 404);
        }

        if (mb_strtolower($modelName) === 'followout') {
            $exists = $authUser->saved_followouts()->where('favoriteable_id', $model->id)->exists();
        }

        if (!$exists) {
            $favorite = new Favorite;
            $favorite->user()->associate($authUser);
            $favorite->favoriteable()->associate($model);
            $favorite->save();
        }

        return response()->json([ 'status' => 'OK' ]);
    }

    public function unfavorite($modelName, $modelId)
    {
        $authUser = auth()->guard('api')->user();

        if (mb_strtolower($modelName) === 'followout') {
            $model = Followout::find($modelId);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Model doesn\'t exist.',
            ], 404);
        }

        if (is_null($model)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Model not found.',
            ], 404);
        }

        if (mb_strtolower($modelName) === 'followout') {
            $authUser->saved_followouts()->where('favoriteable_id', $model->id)->delete();
        }

        return response()->json([ 'status' => 'OK' ]);
    }
}
