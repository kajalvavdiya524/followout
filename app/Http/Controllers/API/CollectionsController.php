<?php

namespace App\Http\Controllers\API;

use GooglePlacesHelper;
use App\Country;
use App\Product;
use App\StaticContent;
use App\FollowoutCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\FollowoutCategoryResource;
use Illuminate\Http\Request;

class CollectionsController extends Controller
{
    public function countries()
    {
        $data = Country::orderBy('name')->get();

        return response()->json([
            'status' => 'OK',
            'data' => $data,
        ]);
    }

    public function experienceCategories()
    {
        return response()->json([
            'status' => 'OK',
            'data' => FollowoutCategoryResource::collection(FollowoutCategory::orderBy('name')->get()),
        ]);
    }

    public function products()
    {
        $data = Product::all();

        return response()->json([
            'status' => 'OK',
            'data' => $data,
        ]);
    }

    public function googlePlacesTypes()
    {
        $data = GooglePlacesHelper::getFilterablePlaceTypesForSelect();

        return response()->json([
            'status' => 'OK',
            'data' => $data,
        ]);
    }

    public function staticContent()
    {
        $data = StaticContent::all();

        return response()->json([
            'status' => 'OK',
            'data' => $data,
        ]);
    }
}
