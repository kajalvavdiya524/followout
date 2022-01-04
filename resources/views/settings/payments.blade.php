@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    @include('settings.tabs')

                    @if (auth()->user()->subscribed())
                        @php
                            $subscriptionProduct = \App\Product::where('type', auth()->user()->subscription->type)->first();
                        @endphp
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">Subscription</div>
                            </div>
                            <div class="Block__body">
                                <div>
                                    <strong>You are subscribed to {{ $subscriptionProduct->name }}!</strong>
                                </div>
                                @if (auth()->user()->subscription->onGracePeriod())
                                    @if (auth()->user()->subscription->isResumable())
                                        <br>
                                        <div class="text-muted">
                                            Your subscription is canceled and is on grace period. You can still resume it if you change your mind.
                                        </div>
                                    @endif
                                    <br>
                                    <div class="text-muted">
                                        Your subscription will end at <strong>{{ auth()->user()->subscription->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}</strong>.
                                    </div>
                                    @unless (auth()->user()->subscription->isResumable())
                                        <br>
                                        <div class="text-muted">
                                            If you wish to cancel your subscription please <a href="javascript:void(0);" data-toggle="modal" data-target="#contact-support-modal">contact our support team</a>.
                                        </div>
                                    @endunless
                                @elseif (auth()->user()->subscription->expires_at)
                                    <br>
                                    <div class="text-muted">
                                        Your subscription will end at <strong>{{ auth()->user()->subscription->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}</strong>.
                                    </div>
                                @elseif (auth()->user()->subscription->isBasic())
                                    <br>
                                    <div class="text-muted">
                                        Your subscription doesn't allow for Coupon, Deal, Offers posts.
                                        <br>
                                        Feel free to upgrade to <a href="{{ route('cart.add', ['product' => $appData['monthlySubscription']->id]) }}">Followouts Pro monthly</a> or <a href="{{ route('cart.add', ['product' => $appData['yearlySubscription']->id]) }}">Followouts Pro annual</a>.
                                    </div>
                                @endif
                            </div>
                            @if (!auth()->user()->subscription->isBasic() && auth()->user()->subscription->isResumable())
                                <div class="Block__footer">
                                    @if (auth()->user()->subscription->onGracePeriod())
                                        <a href="{{ route('subscription.resume') }}" class="Button Button--danger">Resume subscription</a>
                                    @else
                                        <a href="{{ route('subscription.cancel') }}" class="Button Button--danger">Cancel subscription</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">Subscription</div>
                            </div>
                            <div class="Block__body">
                                <div class="text-muted">
                                    You don't have an active subscription right now.
                                </div>
                            </div>
                            @if (auth()->user()->subscription === null)
                                <div class="Block__footer">
                                    <a href="{{ route('pricing') }}" class="Button Button--danger">Subscribe</a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @unless (auth()->user()->subscribed())
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    Subscription code
                                </div>
                            </div>
                            <div class="Block__body">
                                <form class="Form form-horizontal" role="form" method="POST" action="{{ route('settings.subscription-code.activate') }}">
                                    {{ csrf_field() }}

                                    <div class="form-group {{ $errors->has('subscription_code') ? 'has-error' : '' }}">
                                        <label for="subscription_code" class="col-md-4 control-label">Code</label>

                                        <div class="col-md-6">
                                            <input id="subscription_code" type="text" class="form-control" name="subscription_code" placeholder="Your subscription code" required>

                                            @if ($errors->has('subscription_code'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('subscription_code') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group form-group--last">
                                        <div class="col-md-6 col-md-offset-4">
                                            <button type="submit" class="Button Button--danger">
                                                Activate
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endunless

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Sales Representative
                            </div>
                        </div>
                        <div class="Block__body">
                            @if (auth()->user()->wasInvitedBySalesRep())
                                <strong>Sales representative code:</strong> {{ auth()->user()->sales_rep_promo_code ?: auth()->user()->sales_rep_code }}
                            @else
                                <form class="Form form-horizontal" role="form" method="POST" action="{{ route('settings.sales-rep') }}">
                                    {{ csrf_field() }}

                                    <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                                        <label for="code" class="col-md-4 control-label">Code</label>

                                        <div class="col-md-6">
                                            <input id="code" type="text" class="form-control" name="code" placeholder="Sales representative code" required>

                                            @if ($errors->has('code'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('code') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group form-group--last">
                                        <div class="col-md-6 col-md-offset-4">
                                            <button type="submit" class="Button Button--danger">
                                                Apply code
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">Payment history</div>
                        </div>
                        <div class="Block__body">
                            @forelse ($payments as $payment)
                                <div>
                                    <strong>Payment ID:</strong> <a href="{{ route('payments.show', ['payment' => $payment->id]) }}">{{ $payment->payment_id }}</a>
                                </div>
                                <div>
                                    <strong>Paid total:</strong> ${{ $payment->amount }}
                                </div>
                                <div>
                                    <strong>Date:</strong> {{ $payment->created_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}
                                </div>
                                @unless ($loop->last)
                                    <hr>
                                @endunless
                            @empty
                                <div class="text-muted">
                                    You don't have any payments.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
