@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-md-4 col-lg-3">
                    <h2 class="University__heading University__heading--text-left">
                        NO MORE!
                    </h2>
                    <div class="University__item-description">
                        <ul class="University__feature-list University__feature-list--no-more">
                            <li class="University__feature">
                                Charging commissions.
                            </li>
                            <li class="University__feature">
                                Cutting into small business profits.
                            </li>
                            <li class="University__feature">
                                Expensive marketing.
                            </li>
                            <li class="University__feature">
                                Negative voucher experiences.
                            </li>
                            <li class="University__feature">
                                Time consuming campaign work.
                            </li>
                            <li class="University__feature">
                                Unfair postings or reviews.
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xs-12 col-md-8 col-lg-9">
                    <div style="background: #f7f7f7; border-radius: 3px; padding: 15px;">
                        <h2 class="University__heading text-center">
                            Subscribe Now
                        </h2>
                        @if (auth()->guest() || auth()->user()->subscribed())
                            <div class="University__subscribe-buttons ButtonRow">
                                <a href="{{ route('register') }}" class="Button Button--primary">
                                    Free
                                </a>
                                {{-- <a href="{{ route('register', ['plan' => 'basic']) }}" class="Button Button--primary">
                                    Freelancers
                                </a> --}}
                                <div class="Button Button--primary" data-toggle="modal" data-target="#buy-monthly-subscription-code-modal">
                                    One month
                                </div>
                                <div class="Button Button--primary" data-toggle="modal" data-target="#buy-yearly-subscription-code-modal">
                                    One year
                                </div>
                            </div>
                        @else
                            <div class="University__subscribe-buttons ButtonRow">
                                {{-- <a href="{{ route('cart.add', ['product' => $appData['basicSubscription']->id]) }}" class="Button Button--primary">
                                    Freelancers
                                </a> --}}
                                <a href="{{ route('cart.add', ['product' => $appData['monthlySubscription']->id]) }}" class="Button Button--primary">
                                    One month
                                </a>
                                <a href="{{ route('cart.add', ['product' => $appData['yearlySubscription']->id]) }}" class="Button Button--primary">
                                    One year
                                </a>
                            </div>
                        @endif
                        <div class="University__subscribe-buttons-description">
                            @if (auth()->guest() || auth()->user()->subscribed())
                                <div class="text-center" style="flex: 1;">
                                    <span class="text-bold">Try it free!</span>
                                    <br>
                                    <span class="text-muted">(Unlimited Followouts)</span>
                                    <br>
                                    <span class="text-muted">(Promote to Followout community)</span>
                                </div>
                            @endif
                            {{-- <div class="text-center" style="flex: 1;">
                                <span class="text-bold">
                                    ${{ number_format($appData['basicSubscription']->price, 2) }} / non-recurring
                                </span>
                                <br>
                                <span class="text-muted">(Unlimited)</span>
                                <br>
                                <span class="text-muted">(Promote to everyone)</span>
                                <br>
                                <br>
                                Great for personal brands!
                            </div> --}}
                            <div class="text-center" style="flex: 1;">
                                <span class="text-bold">
                                    ${{ number_format($appData['monthlySubscription']->price, 2) }} / mo.
                                </span>
                                <br>
                                <span class="text-muted">(Unlimited)</span>
                                <br>
                                <span class="text-muted">(Promote to everyone)</span>
                            </div>
                            <div class="text-center" style="flex: 1;">
                                <span class="text-bold">
                                    ${{ number_format($appData['yearlySubscription']->price / 12, 2) }} / mo.
                                </span>
                                <br>
                                <span class="text-muted">(Unlimited)</span>
                                <br>
                                <span class="text-muted">(Promote to everyone)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="University__top-heading">
                        How it works
                    </h1>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12 col-md-4">
                    <div class="University__image-wrap">
                        <img src="{{ url('/img/university/FOLLOWOUTUNIVERSITY_CREATE_FOLLOWOUT.png') }}" class="University__image">
                    </div>
                    <h2 class="University__heading hidden-md hidden-lg">
                        1. CREATE FOLLOWOUT
                    </h2>
                    <div class="University__item-description hidden-md hidden-lg">
                        Post Your Location
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="University__image-wrap">
                        <img src="{{ url('/img/university/MOBILEDETAILPAGE_FOLLOWEES.png') }}" class="University__image">
                    </div>
                    <h2 class="University__heading hidden-md hidden-lg">
                        2. ENHANCE FOLLOWOUT
                    </h2>
                    <div class="University__item-description hidden-md hidden-lg">
                        Images, GIFs, Videos, Coupons
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="University__image-wrap">
                        <img src="{{ url('/img/university/FOLLOWOUTUNIVERSITY_MEASUREFOLLOWOUT.png') }}" class="University__image">
                    </div>
                    <h2 class="University__heading hidden-md hidden-lg">
                        3. MEASURE FOLLOWOUT
                    </h2>
                    <div class="University__item-description hidden-md hidden-lg">
                        Send Invites, Count Foot Traffic
                    </div>
                </div>
            </div>
            <div class="row hidden-xs hidden-sm">
                <div class="col-xs-12 col-md-4">
                    <h2 class="University__heading">
                        1. CREATE FOLLOWOUT
                    </h2>
                    <div class="University__item-description">
                        Post Your Location
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <h2 class="University__heading">
                        2. ENHANCE FOLLOWOUT
                    </h2>
                    <div class="University__item-description">
                        Images, GIFs, Videos, Coupons
                    </div>
                </div>
                <div class="col-xs-12 col-md-4">
                    <h2 class="University__heading">
                        3. MEASURE FOLLOWOUT
                    </h2>
                    <div class="University__item-description">
                        Send Invites, Count Foot Traffic
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (!is_null($data['university']) && $data['university']->marketing_video_url)
        <div id="video" class="Section">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                        <div class="text-center">
                            @if ($data['university']->marketing_video_title)
                                <h2 class="University__heading">
                                    {{ $data['university']->marketing_video_title }}
                                </h2>
                            @endif

                            @if ($data['university']->marketing_video_thumb_url)
                                <video class="w-100" src="{{ $data['university']->marketing_video_url }}" poster="{{ $data['university']->marketing_video_thumb_url }}" preload="metadata" controls></video>
                            @else
                                <video class="w-100" src="{{ $data['university']->marketing_video_url }}" preload="metadata" controls></video>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@include('includes.gtag-report-conversion-guest-subscription')

@push('modals')
    @include('includes.modals.buy-monthly-subscription-code')
    @include('includes.modals.buy-yearly-subscription-code')
@endpush
