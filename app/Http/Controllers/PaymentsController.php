<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Payment;
use App\Product;

class PaymentsController extends Controller
{
    public function index()
    {
        $payments = Payment::orderBy('created_at', 'DESC')->get();

        return view('orders.payments', compact('payments'));
    }

    public function show(Payment $payment)
    {
        $hasAccess = auth()->user()->payments()->where('_id', $payment->id)->first() || auth()->user()->isAdmin();

        if (!$hasAccess) {
            return abort(404);
        }

        return view('orders.show', compact('payment'));
    }
}
