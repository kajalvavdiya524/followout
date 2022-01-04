@extends('layouts.app')

@section('page-title', $followout->title)

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-offset-3 col-md-6">
                    <div class="ProfileLinkedItems" style="background-color: #f3f3f3;">
                        <div class="ProfileLinkedItems__title">GEO Coupon Followout Preview</div>
                        <div class="text-muted text-center" style="padding: 0 15px;">
                            Create GEO Coupon Followout to offer your visitors sweet deals with a single click.
                            <br>
                            <br>
                            This is a preview of a Followout you are about to create.
                            <br>
                            <br>
                            Your subscribers and the Followout Community will be notified and your Followout will end on {{ $followout->coupon->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}.
                        </div>
                        <div class="ButtonRow" style="padding: 15px;">
                            <a href="{{ route('coupons.create-followout', ['coupon' => $followout->coupon->id]) }}" class="Button Button--sm Button--danger">Create Followout</a>
                            <div class="ButtonRow__or"></div>
                            <a href="{{ route('coupons.index') }}" class="Button Button--sm Button--default">Go back</a>
                        </div>
                    </div>
                </div>
            </div>

            <br>
            <hr>
            <br>

            <div class="row">
                <div class="col-md-4">
                    <div class="ProfilePicture">
                        @if ($followout->flyer_url)
                            <img class="ProfilePicture__picture" src="{{ $followout->flyer_url }}" />
                        @else
                            <img class="ProfilePicture__picture" src="{{ $followout->defaultFlyerURL() }}" />
                        @endif
                    </div>
                    <div class="ProfileButtonsWrap">
                        <div class="Button Button--block Button--danger">
                            <i class="fas fa-fw fa-chart-bar"></i>
                            View stats
                        </div>
                        {{-- Followout managing --}}
                        @if (Gate::allows('invite-followee-by-email'))
                            <div class="Button Button--block Button--danger">
                                <i class="far fa-fw fa-envelope"></i>
                                Invite a Followout presenter by email
                            </div>
                        @endif
                        <div class="Button Button--block Button--danger">
                            <i class="fas fa-fw fa-pencil-alt"></i>
                            Enhance Followout
                        </div>
                        <div class="Button Button--block Button--danger">
                            <i class="fas fa-fw fa-ticket-alt"></i>
                            Link GEO Coupons, Deals, Offers
                        </div>
                        <div class="Button Button--block Button--danger">
                            <i class="far fa-fw fa-trash-alt"></i>
                            Delete Followout
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="ProfileInfo">
                        <div class="ProfileInfo__heading">{{ $followout->title }}</div>
                        <div class="ProfileInfo__user-type">
                            By <a href="javascript:void(0);">{{ $followout->author->name }}</a>
                        </div>
                        <div class="ProfileInfo__meta">
                            @if ($followout->hasEnded())
                                <hr>
                                <div class="ProfileInfo__meta-item">
                                    <i class="far fa-fw fa-calendar-check"></i>
                                    Followout has ended and has been archived
                                </div>
                                <hr>
                            @elseif ($followout->isOngoing() && !$followout->isDefault())
                                <hr>
                                <div class="ProfileInfo__meta-item">
                                    <i class="far fa-fw fa-calendar-check"></i>
                                    {{-- If it's a reposted default Followout --}}
                                    @if ($followout->ends_at >= now()->addYear())
                                        Followout has started
                                    @else
                                        Followout has started and will end in {{ $followout->ends_at->tz(session_tz())->diffForHumans() }}
                                    @endif
                                </div>
                                <hr>
                            @endif
                            @if ($followout->isPrivate())
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-lock"></i>
                                    Only visible to invited users
                                </div>
                            @elseif ($followout->isFollowersOnly())
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-lock"></i>
                                    Only visible to Followout Community
                                </div>
                            @endif
                            @if ($followout->isGeoCoupon())
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-ticket-alt"></i>
                                    This is <a href="javascript:void(0);">GEO Coupon</a> Followout
                                </div>
                            @endif
                            @unless ($followout->isDefault())
                                <div class="ProfileInfo__meta-item">
                                    <i class="far fa-fw fa-calendar"></i>
                                    {{ $followout->starts_at->tz(session_tz())->format('l, F jS') }}
                                    <i class="far fa-fw fa-clock" style="margin-left: 10px;"></i>
                                    {{ $followout->starts_at->tz(session_tz())->format(config('followouts.time_format')) }}
                                </div>
                            @endunless
                        </div>

                        <div class="ProfileInfo__focus">
                            <p class="ProfileInfo__focus-item">
                                <strong>Experience:</strong> {{ implode(', ', $followout->experience_categories->pluck('name')->toArray()) }}
                            </p>
                            @if ($followout->isVirtual())
                                <p class="ProfileInfo__focus-item">
                                    <strong>Virtual Location:</strong> <a target="_blank" href="javascript:void(0);" title="{{ $followout->virtual_address }}">{{ get_domain($followout->virtual_address) }}</a>
                                </p>
                            @else
                                <p class="ProfileInfo__focus-item">
                                    <strong>Location:</strong> {{ $followout->fullAddress() }}
                                </p>
                            @endif
                        </div>

                        <div class="ProfileInfo__meta">
                            @unless ($followout->isDefault())
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-play-circle"></i>
                                    Starts at {{ $followout->starts_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}
                                </div>
                                @unless ($followout->ends_at > Carbon::now()->addYear())
                                    <div class="ProfileInfo__meta-item">
                                        <i class="fas fa-fw fa-stop-circle"></i>
                                        Ends at {{ $followout->ends_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}
                                    </div>
                                @endunless
                            @endunless
                            @if ($followout->tickets_url)
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-external-link-alt"></i>
                                    <a target="_blank" href="{{ $followout->tickets_url }}">Tickets</a>
                                </div>
                            @endif
                            @if ($followout->external_info_url)
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-external-link-alt"></i>
                                    <a target="_blank" href="{{ $followout->external_info_url }}">Other information</a>
                                </div>
                            @endif
                        </div>

                        @unless ($followout->isVirtual())
                            <div class="ProfileInfo__map">
                                <div id="map"></div>
                            </div>
                        @endunless

                        <div class="ProfileInfo__about">
                            @unless ($followout->isGeoCoupon())
                                <div>
                                    <strong>Followout description</strong>
                                </div>

                                {!! nl2br(e($followout->description)) !!}
                            @else
                                <div>
                                    <strong>GEO Coupon</strong>
                                </div>

                                @if ($followout->coupon->promo_code)
                                    <div>
                                        <span class="text-semibold">Promo code:</span>
                                        {{ $followout->coupon->promo_code }}
                                    </div>
                                @endif

                                <div>
                                    <span class="text-semibold">Value:</span>
                                    {{ $followout->coupon->discount_value_formatted }}
                                </div>

                                <div>
                                    <span class="text-semibold">Expiration date:</span>
                                    {{ $followout->coupon->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string_long')) }}
                                </div>

                                <div>
                                    <span class="text-semibold">Details:</span>
                                    {{ $followout->coupon->description }}
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    {{-- Followees --}}
                    <div class="ProfileLinkedItems">
                        <div class="ProfileLinkedItems__title">Followees</div>
                        <div class="text-muted text-center">
                            There are no Followees yet.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-footer')
    @unless ($followout->isVirtual())
        @include('includes.google-maps')

        <script>
            currentLocation = {
                lat: {{ $followout->lat }},
                lng: {{ $followout->lng }},
            };
        </script>

        <script> $(function() { initMap() }); </script>
    @endif
@endpush
