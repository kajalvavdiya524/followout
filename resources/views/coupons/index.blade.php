@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                GEO Coupons, Deals, Offers
                            </div>
                        </div>
                        <div class="Block__body Block__body--no-padding">
                            <div class="text-center" style="padding: 15px 0;">
                                <div class="ButtonGroup ButtonGroup--center">
                                    @if (auth()->user()->subscribed() || auth()->user()->isAdmin())
                                        <a href="{{ route('coupons.create') }}" class="Button Button--danger">
                                            Create GEO Coupons, Deals, Offers
                                        </a>
                                    @else
                                        <div class="Button Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                            Create GEO Coupons, Deals, Offers
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="FollowoutCouponList clearfix">
                                @forelse ($coupons as $coupon)
                                    <div class="FollowoutCouponListItem">
                                        <div class="FollowoutCouponListItem__picture-wrap">
                                            <img src="{{ $coupon->pictureURL() }}" alt="Coupon" class="FollowoutCouponListItem__picture">
                                        </div>
                                        <div class="FollowoutCouponListItem__content">
                                            <div class="FollowoutCouponListItem__heading">
                                                {{ $coupon->title }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Details:</span>
                                                {{ $coupon->description }}
                                            </div>
                                            @if ($coupon->discount)
                                                <div class="FollowoutCouponListItem__description">
                                                    <span class="text-semibold">Value:</span>
                                                    {{ $coupon->discount_value_formatted }}
                                                </div>
                                            @endif
                                            @if ($coupon->code)
                                                <div class="FollowoutCouponListItem__description">
                                                    <span class="text-semibold">Internal code:</span>
                                                    {{ $coupon->code }}
                                                </div>
                                            @endif
                                            @if ($coupon->promo_code)
                                                <div class="FollowoutCouponListItem__description">
                                                    <span class="text-semibold">Promo code:</span>
                                                    {{ $coupon->promo_code }}
                                                </div>
                                            @endif
                                            @if ($coupon->expires_at)
                                                <div class="FollowoutCouponListItem__description">
                                                    <span class="text-semibold">Expiration date:</span>
                                                    {{ $coupon->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}
                                                    @if ($coupon->expires_at <= Carbon::now())
                                                        <span class="text-danger">(expired)</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="FollowoutCouponListItem__used">
                                                <span class="text-semibold">Coupons used:</span>
                                                {{ $coupon->useCount() }}
                                            </div>
                                            @if ($coupon->legacy())
                                                <div class="FollowoutCouponListItem__description text-muted">
                                                    Note: this is a legacy coupon that doesn't support creating GEO Followouts.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="FollowoutCouponListItem__manage-buttons">
                                            @if ($coupon->followout)
                                                <a href="{{ $coupon->followout->url() }}" class="Button Button--sm Button--danger">
                                                    View Followout
                                                </a>
                                            @elseif (!$coupon->legacy() && auth()->user()->isFollowhost())
                                                @if (auth()->user()->subscribedToPro())
                                                    <a href="{{ route('followouts.preview.geo', ['coupon' => $coupon->id]) }}" class="Button Button--sm Button--danger">
                                                        Create Followout
                                                    </a>
                                                @elseif (auth()->user()->subscribedToBasic())
                                                    <div class="Button Button--sm Button--danger" data-toggle="modal" data-target="#subscription-upgrade-required-modal">
                                                        Create Followout
                                                    </div>
                                                @else
                                                    <div class="Button Button--sm Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                                        Create Followout
                                                    </div>
                                                @endif
                                            @endif
                                            @php
                                                $deleteCouponModalId = 'delete-geo-coupon-modal-'.$coupon->id;
                                            @endphp
                                            <div class="Button Button--sm Button--danger" data-toggle="modal" data-target="#{{ $deleteCouponModalId }}">
                                                Delete Coupon
                                            </div>
                                            @push('modals')
                                                @include('includes.modals.delete-geo-coupon', ['modalId' => $deleteCouponModalId])
                                            @endpush
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <div class="text-muted text-center" style="padding: 0 0 15px 0;">
                                                Create GEO coupons for your Followouts to offer your visitors sweet deals.
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
