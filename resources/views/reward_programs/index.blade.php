@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Reward Programs
                            </div>
                        </div>
                        <div class="Block__body Block__body--no-padding">
                            <div class="text-center" style="{{ $rewardPrograms->isNotEmpty() ? 'padding: 15px 0;' : 'padding: 15px 0 0 0;' }}">
                                <div class="ButtonGroup ButtonGroup--center">
                                    @if (Gate::allows('manage-reward-programs'))
                                        @if (auth()->user()->hasOpenDisputes())
                                            <a class="Button Button--danger" href="javascript:void(0);" data-toggle="modal" data-target="#resolve-pending-disputes-modal">
                                                Create Reward Program
                                            </a>
                                            @push('modals')
                                                @include('includes.modals.resolve-pending-disputes')
                                            @endpush
                                        @else
                                            <a class="Button Button--danger" href="{{ route('reward_programs.create') }}">
                                                Create Reward Program
                                            </a>
                                        @endif
                                    @else
                                        <a class="Button Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                            Create Reward Program
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="FollowoutCouponList clearfix">
                                @forelse ($rewardPrograms as $rewardProgram)
                                    <div class="FollowoutCouponListItem">
                                        <div class="FollowoutCouponListItem__picture-wrap">
                                            <img src="{{ $rewardProgram->pictureURL() }}" alt="Coupon" class="FollowoutCouponListItem__picture">
                                        </div>
                                        <div class="FollowoutCouponListItem__content">
                                            <div class="FollowoutCouponListItem__heading">
                                                {{ $rewardProgram->title }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Followout:</span>
                                                {{ $rewardProgram->followout->title }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Reward:</span>
                                                {{ $rewardProgram->description }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Redeem count:</span>
                                                {{ $rewardProgram->redeem_count }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Redeem code:</span>
                                                {{ $rewardProgram->redeem_code }}
                                            </div>
                                            <div class="FollowoutCouponListItem__description">
                                                <span class="text-semibold">Redeem by date:</span>
                                                {{ $rewardProgram->followout->ends_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}
                                                @if ($rewardProgram->followout->ends_at <= Carbon::now())
                                                    <span class="text-danger">(expired)</span>
                                                @endif
                                            </div>
                                            <div class="FollowoutCouponListItem__used">
                                                <span class="text-semibold">Followees:</span>
                                                {{ $rewardProgram->followout->followees->count() }}
                                            </div>
                                            <div class="FollowoutCouponListItem__used">
                                                <span class="text-semibold">Checkins:</span>
                                                {{ $rewardProgram->getTotalCheckinsCount() }}
                                            </div>
                                            @if ($rewardProgram->require_coupon)
                                                <div class="FollowoutCouponListItem__used">
                                                    <span class="text-semibold">Coupon required:</span>
                                                    <span>{{ $rewardProgram->require_coupon ? 'Yes' : 'No' }}</span>
                                                </div>
                                                <div class="FollowoutCouponListItem__used">
                                                    <span class="text-semibold">Attached coupon(s):</span>
                                                    @if ($rewardProgram->followout->coupons->isNotEmpty())
                                                        <span>{{ $rewardProgram->followout->coupons->pluck('title')->implode(',') }}</span>
                                                    @else
                                                        <span class="text-danger">Please attach a valid coupon to followout</span>
                                                    @endif
                                                </div>
                                            @endif
                                            <div class="FollowoutCouponListItem__used">
                                                <span class="text-semibold">Status:</span>
                                                <span class="{{ $rewardProgram->isActive() ? 'text-success' : 'text-danger' }}">{{ $rewardProgram->isActive() ? 'Active' : 'Paused' }}</span>
                                            </div>
                                        </div>
                                        <div class="FollowoutCouponListItem__manage-buttons">
                                            <a href="{{ $rewardProgram->followout->url() }}" class="Button Button--sm Button--danger">
                                                View Followout
                                            </a>
                                            @if ($rewardProgram->canBeUpdated())
                                                <a href="{{ route('reward_programs.edit', ['reward_program' => $rewardProgram->id]) }}" class="Button Button--sm Button--danger">
                                                    Edit Program
                                                </a>
                                            @endif
                                            @php
                                                $modalId = 'pause-reward-program-' . $rewardProgram->id;
                                            @endphp
                                            @if ($rewardProgram->isActive())
                                                <div class="Button Button--sm Button--danger" data-toggle="modal" data-target="#{{ $modalId }}">
                                                    Pause Program
                                                </div>
                                                @push('modals')
                                                    @include('includes.modals.pause-reward-program', ['modalId' => $modalId])
                                                @endpush
                                            @else
                                                <a href="{{ route('reward_programs.resume', ['rewardProgram' => $rewardProgram->getKey()]) }}" class="Button Button--sm Button--danger">
                                                    Resume Program
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="form-group">
                                        <div class="col-xs-12">
                                            <div class="text-muted text-center" style="padding: 15px 0;">
                                                Create reward programs for followees to promote your Followouts.
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
