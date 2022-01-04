<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function resume(Request $request)
    {
        if (!auth()->user()->subscribed()) {
            return abort(403, 'Access denied.');
        }

        if (auth()->user()->subscription->isResumable() && auth()->user()->subscription->onGracePeriod()) {
            auth()->user()->subscription->resume();

            session()->flash('toastr.success', 'Your subscription has been resumed.');
        }

        return redirect()->route('settings.payments');
    }

    public function cancel(Request $request)
    {
        if (!auth()->user()->subscribedToPro()) {
            return abort(403, 'Access denied.');
        }

        if (!auth()->user()->subscription->isCanceled()) {
            auth()->user()->subscription->cancel();

            session()->flash('toastr.success', 'Your subscription has been canceled.');
        }

        return redirect()->route('settings.payments');
    }
}
