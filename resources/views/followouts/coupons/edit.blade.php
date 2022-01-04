@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="StatsFollowoutHeading">
                        <img src="{{ $followout->flyerURL() }}" class="StatsFollowoutHeading__flyer StatsFollowoutHeading__flyer--no-margin-left">
                        {{ $followout->title }} GEO Coupons
                    </div>

                    <br>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Active GEO Coupons
                            </div>
                        </div>
                        <div class="Block__body Block__body--no-padding">
                            <div class="FollowoutCouponList clearfix">
                                @forelse ($usedCoupons as $coupon)
                                    <div class="FollowoutCouponListItem">
                                        <div class="FollowoutCouponListItem__picture-wrap">
                                            <img src="{{ $coupon->pictureURL() }}" alt="Coupon" class="FollowoutCouponListItem__picture">
                                        </div>
                                        <div class="FollowoutCouponListItem__content">
                                            <div class="FollowoutCouponListItem__heading">
                                                {{ $coupon->title }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                {{ $coupon->description }}
                                            </div>
                                            <div class="FollowoutCouponListItem__usage">
                                                <div class="FollowoutCouponListItem__used">
                                                    <span class="text-semibold">Coupons used:</span>
                                                    {{ $coupon->useCount() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="FollowoutCouponListItem__manage-buttons">
                                            <a href="{{ route('followouts.coupons.disable', ['followout' => $followout->id, 'coupon' => $coupon->coupon->id]) }}" class="Button Button--sm Button--danger" data-method="post" data-token="{{ csrf_token() }}" data-confirm="Are you sure you want to stop using this coupon?">
                                                Disable coupon
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <div class="text-muted text-center" style="padding: 30px 0;">You don't have any attached GEO coupons.</div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if ($unusedCoupons->count() > 0)
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    Inactive GEO Coupons
                                </div>
                            </div>
                            <div class="Block__body Block__body--no-padding">
                                <div class="FollowoutCouponList clearfix">
                                    @foreach ($unusedCoupons as $coupon)
                                        <div class="FollowoutCouponListItem">
                                            <div class="FollowoutCouponListItem__picture-wrap">
                                                <img src="{{ $coupon->pictureURL() }}" alt="Coupon" class="FollowoutCouponListItem__picture">
                                            </div>
                                            <div class="FollowoutCouponListItem__content">
                                                <div class="FollowoutCouponListItem__heading">
                                                    {{ $coupon->title }}
                                                </div>
                                                <div class="FollowoutCouponListItem__description">
                                                    {{ $coupon->description }}
                                                </div>
                                            </div>
                                            <div class="FollowoutCouponListItem__manage-buttons">
                                                <a href="{{ route('followouts.coupons.use', ['followout' => $followout->id, 'coupon' => $coupon->id]) }}" class="Button Button--sm Button--danger" data-method="post" data-token="{{ csrf_token() }}" data-confirm="Are you sure you want to enable this coupon?">
                                                    Enable coupon
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Your GEO Coupons
                            </div>
                        </div>
                        <div class="Block__body Block__body--no-padding">
                            <div class="FollowoutCouponList clearfix">
                                @forelse ($otherCoupons as $coupon)
                                    <div class="FollowoutCouponListItem">
                                        <div class="FollowoutCouponListItem__picture-wrap">
                                            <img src="{{ $coupon->pictureURL() }}" alt="Coupon" class="FollowoutCouponListItem__picture">
                                        </div>
                                        <div class="FollowoutCouponListItem__content">
                                            <div class="FollowoutCouponListItem__heading">
                                                {{ $coupon->title }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                {{ $coupon->description }}
                                            </div>
                                        </div>
                                        <div class="FollowoutCouponListItem__manage-buttons">
                                            <a href="{{ route('followouts.coupons.use', ['followout' => $followout->id, 'coupon' => $coupon->id]) }}" class="Button Button--sm Button--danger" data-method="post" data-token="{{ csrf_token() }}" data-confirm="Are you sure you want to link this coupon?">
                                                Link coupon
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <div class="text-muted text-center" style="padding: 30px 0;">You don't have any other GEO coupons.</div>
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
