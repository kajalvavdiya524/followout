<?php

namespace App\Http\Controllers;

use App\Product;
use App\PromoCode;
use Illuminate\Http\Request;

class ProductsController extends Controller
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
        $products = Product::all();
        $promoCodes = PromoCode::all();

        return view('admin.products.index', compact('products', 'promoCodes'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request, [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:250',
            'price' => 'required|regex:/^\d*(\.\d{2})?$/',
        ]);

        if ($request->input('price', 0) <= 0) {
            session()->flash('toastr.error', 'Price should be bigger than 0.');
            return redirect()->back()->withInput();
        }

        $product->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => number_format((float) $request->input('price'), 2, '.', ''),
        ]);

        session()->flash('toastr.success', 'Product has been updated.');

        return redirect()->route('products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPromoCode()
    {
        return view('admin.promo-codes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePromoCode(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|string|max:250|unique:promo_codes,code',
            'amount' => 'required|regex:/^\d*(\.\d{2})?$/',
        ]);

        if ($request->input('amount', 0) <= 0) {
            session()->flash('toastr.error', 'Amount should be bigger than 0.');
            return redirect()->back()->withInput();
        }

        $code = new PromoCode;
        $code->code = $request->input('code');
        $code->amount = number_format($request->input('amount'), 2, '.', '');
        $code->save();

        session()->flash('toastr.success', 'Promo code has been saved.');

        return redirect()->route('products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\PromoCode  $code
     * @return \Illuminate\Http\Response
     */
    public function destroyPromoCode(PromoCode $code)
    {
        $code->delete();

        session()->flash('toastr.success', 'Promo code has been deleted.');

        return redirect()->route('products.index');
    }
}
