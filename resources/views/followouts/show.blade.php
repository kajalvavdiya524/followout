@extends('layouts.app')

@section('page-title', $followout->title)

@section('content')
    <div class="Section">
        <div class="container">
            @php
                $followee = auth()->check() ? $followout->followees()->pending()->notRequestedByUser()->where('user_id', auth()->user()->id)->first() : null;
            @endphp
            @if (auth()->check() && !is_null($followee) && $followout->isUpcomingOrOngoing())
                <div class="row">
                    <div class="col-md-offset-3 col-md-6">
                        <div class="ProfileLinkedItems" style="background-color: #f3f3f3;">
                            <div class="ProfileLinkedItems__title">Congratulations!</div>
                            <div class="text-muted" style="padding: 0 15px;">
                                You have been invited to present this Followout
                                <br>
                                <br>
                                @if ($followee->reward_program)
                                    Reward program: {{ $followee->reward_program->title }}
                                    <br>
                                    Reward: {{ $followee->reward_program->description }}
                                    <br>
                                    Required attendee count: {{ $followee->reward_program->redeem_count }}
                                    <br>
                                    <br>
                                @endif
                                To confirm you'll present this Followout, click <strong>Accept</strong>.
                            </div>
                            <div class="ButtonRow" style="padding: 15px;">
                                <a href="{{ route('followouts.invitation.accept', ['followout' => $followout->id]) }}" class="Button Button--sm Button--danger">Accept</a>
                                <a href="{{ route('followouts.invitation.decline', ['followout' => $followout->id]) }}" class="Button Button--sm Button--primary">Decline</a>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <hr>
                <br>
            @endif

            @if (auth()->check() && $followout->followees()->requestedByUser()->where('user_id', auth()->user()->id)->exists())
                <div class="row">
                    <div class="col-md-offset-3 col-md-6">
                        <div class="ProfileLinkedItems" style="background-color: #f3f3f3;">
                            <div class="ProfileLinkedItems__title">Present Followout request submitted</div>
                            <div class="text-muted" style="padding: 0 15px 15px;">
                                @php
                                    $followee = $followout->followees()->requestedByUser()->where('user_id', auth()->user()->id)->first();
                                @endphp
                                @unless ($followee->isAccepted())
                                    You have submitted a request to present this Followout.
                                    <br>
                                    <br>
                                @endunless
                                @if ($followee->isPending())
                                    We'll let you know if your request is accepted or declined.
                                @elseif ($followee->isAccepted())
                                    Congratulations! Your request has been accepted.
                                @elseif ($followee->isDeclined())
                                    Sorry! Your request has been declined.
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <hr>
                <br>
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="ProfilePicture">
                        @if ($followout->hasFlyer())
                            @if ($followout->hasVideoFlyer())
                                @if ($followout->flyer->video->isProcessed())
                                    <video class="ProfilePicture__picture" onloadstart="this.volume=0.5" src="{{ $followout->videoFlyerURL() }}" style="width: 100%; max-height: 750px; background-color: #F9F9F9; margin-bottom: 15px;" autoplay controls></video>
                                @else
                                    <img class="ProfilePicture__picture" src="{{ $followout->defaultVideoFlyerURL() }}" />
                                @endif
                            @else
                                <img class="ProfilePicture__picture" src="{{ $followout->flyerURL() }}" />
                            @endif
                        @else
                            @if ($followout->defaultFlyerIsVideo())
                                <video class="ProfilePicture__picture" onloadstart="this.volume=0.5" src="{{ $followout->videoFlyerURL() }}" style="width: 100%; max-height: 750px; background-color: #F9F9F9; margin-bottom: 15px;" autoplay controls></video>
                            @else
                                <img class="ProfilePicture__picture" src="{{ $followout->defaultFlyerURL() }}" />
                            @endif
                        @endif
                        @auth
                            @if ($followout->author->id === auth()->user()->id || auth()->user()->isAdmin())
                                @unless ($followout->hasVideoFlyer())
                                    @unless ($followout->hasCompletedCheckins() && !(auth()->user()->isFollowhost() || auth()->user()->isAdmin()))
                                        {{-- Only if not edited yet --}}
                                        @unless ($followout->isEdited())
                                            <a href="{{ route('followouts.edit', ['followout' => $followout->id]) }}" class="ProfilePicture__enhance">
                                                <i class="fas fa-magic"></i>
                                                <br>
                                                Enhance!
                                            </a>
                                        @endunless
                                    @endunless
                                @endunless
                            @endif
                        @endauth
                    </div>
                    @if ($followout->hasPicture())
                        <div class="ProfilePictureThumbs {{ 'ProfilePictureThumbs--'.$followout->pictures()->count() }}">
                            @foreach ($followout->pictures as $key => $picture)
                                <img class="ProfilePictureThumbs__thumb ProfilePictureThumbs__thumb--followout {{ $key == 0 ? 'active' : ''}}" src="{{ $followout->pictureURL($key) }}" />
                            @endforeach
                        </div>
                    @endif
                    @unless ($followout->hasEnded())
                        @if (auth()->check() && (auth()->user()->id === $followout->author->id || auth()->user()->isAdmin() || $followout->hasAcceptedFollowee(auth()->user()->id)))
                            <div class="ProfileShareButtons">
                                <div class="sharethis-inline-share-buttons"
                                    data-url="{{ $followout->isPublic() ? convertToHttpScheme($followout->url()) : convertToHttpScheme($followout->url(true)) }}"
                                    data-title="{{ $followout->title }}"
                                    data-image="{{ convertToHttpScheme($followout->flyerURL()) }}"
                                    data-description="{{ $followout->about }}"
                                ></div>
                            </div>
                        @endif
                    @endunless
                    @if (auth()->check())
                        <div class="ProfileButtonsWrap">
                            @if ($followout->isReposted())
                                @if ($followout->parent_followout->userHasAccess(auth()->user()))
                                    {{-- View original --}}
                                    <a href={{ route('followouts.show', ['followout' => $followout->getTopParentFollowout()->id]) }} class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-external-link-alt"></i>
                                        View original Followout
                                    </a>
                                @endif

                                @if (auth()->user()->id === $followout->author->id || ($followout->isReposted() && auth()->user()->id === $followout->getTopParentFollowout()->author->id) || auth()->user()->id === $followout->author->id || auth()->user()->isAdmin())
                                    <a href="{{ route('followouts.stats', ['followout' => $followout->id]) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-chart-bar"></i>
                                        View stats
                                    </a>
                                @endif

                                {{-- Followout managing --}}
                                @if ($followout->isUpcomingOrOngoing())
                                    @if ($followout->author->id === auth()->user()->id)
                                        <div class="Button Button--danger Button--block" data-toggle="modal" data-target="#invite-friends-modal">
                                            <i class="fas fa-fw fa-users"></i>
                                            Invite Friends
                                        </div>
                                        @push('modals')
                                            @include('includes.modals.invite-friends')
                                        @endpush

                                        @if (Gate::allows('invite-followee-by-email'))
                                            <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#invite-followee-by-email-modal">
                                                <i class="far fa-fw fa-envelope"></i>
                                                Invite a Followout presenter
                                            </div>
                                            @push('modals')
                                                @include('includes.modals.invite-followee-by-email')
                                            @endpush
                                        @endif
                                    @endif
                                @endif

                                @if (auth()->user()->isAdmin() || $followout->author->id === auth()->user()->id)
                                    <a href="{{ route('followouts.edit', ['followout' => $followout->id]) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-pencil-alt"></i>
                                        Enhance Followout
                                    </a>
                                @endif

                                @if ($followout->author->id === auth()->user()->id || $followout->getTopParentFollowout()->author->id === auth()->user()->id || auth()->user()->isAdmin())
                                    <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#delete-followout-modal">
                                        <i class="far fa-fw fa-trash-alt"></i>
                                        Delete Followout
                                    </div>

                                    @push('modals')
                                        @include('includes.modals.delete-followout')
                                    @endpush
                                @endif
                            @else
                                @if (auth()->user()->id === $followout->author->id || ($followout->isReposted() && auth()->user()->id === $followout->getTopParentFollowout()->author->id) || auth()->user()->id === $followout->author->id || auth()->user()->isAdmin())
                                    <a href="{{ route('followouts.stats', ['followout' => $followout->id]) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-chart-bar"></i>
                                        View stats
                                    </a>
                                @endif
                                {{-- Followout managing --}}
                                @if ($followout->author->id === auth()->user()->id || auth()->user()->isAdmin())
                                    @if ($followout->isUpcomingOrOngoing())
                                        @if ($followout->author->id === auth()->user()->id)
                                            <div class="Button Button--danger Button--block" data-toggle="modal" data-target="#invite-friends-modal">
                                                <i class="fas fa-fw fa-users"></i>
                                                Invite Friends
                                            </div>
                                            @push('modals')
                                                @include('includes.modals.invite-friends')
                                            @endpush

                                            @if (Gate::allows('invite-followee-by-email'))
                                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#invite-followee-by-email-modal">
                                                    <i class="far fa-fw fa-envelope"></i>
                                                    Invite a Followout presenter
                                                </div>
                                                @push('modals')
                                                    @include('includes.modals.invite-followee-by-email')
                                                @endpush
                                            @elseif (auth()->user()->isFollowhost() && !auth()->user()->subscribed())
                                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                                     <i class="fas fa-fw fa-street-view"></i>
                                                    Invite a Followout presenter
                                                </div>
                                            @endif
                                        @endif
                                    @endif

                                    @if (auth()->user()->isAdmin() || $followout->author->id === auth()->user()->id)
                                        <a href="{{ route('followouts.edit', ['followout' => $followout->id]) }}" class="Button Button--block Button--danger">
                                            <i class="fas fa-fw fa-pencil-alt"></i>
                                            Enhance Followout
                                        </a>
                                    @endif

                                    @if ($followout->isUpcomingOrOngoing())
                                        @if ($followout->author->id === auth()->user()->id)
                                            <a href="{{ route('followouts.coupons.edit', ['followout' => $followout->id]) }}" class="Button Button--block Button--danger">
                                                <i class="fas fa-fw fa-ticket-alt"></i>
                                                Link GEO Coupons, Deals, Offers
                                            </a>
                                            @if (!$followout->isDefault())
                                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#delete-followout-modal">
                                                    <i class="far fa-fw fa-trash-alt"></i>
                                                    Delete Followout
                                                </div>
                                                @push('modals')
                                                    @include('includes.modals.delete-followout')
                                                @endpush
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            @endif
                        </div>
                    @endif
                </div>

                <div class="col-md-5">
                    <div class="ProfileInfo">
                        <div class="ProfileInfo__heading">{{ $followout->title }}</div>
                        <div class="ProfileInfo__user-type">
                            @if ($followout->isReposted())
                                By <a href="{{ $followout->getTopParentFollowout()->author->url() }}">{{ $followout->getTopParentFollowout()->author->name }}</a>, presented by <a href="{{ $followout->author->url() }}">{{ $followout->author->name }}</a>
                            @else
                                By <a href="{{ $followout->author->url() }}">{{ $followout->author->name }}</a>
                            @endif
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
                            @elseif ($followout->isDefault() && auth()->check() && $followout->author->id === auth()->user()->id)
                                <hr>
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-info-circle"></i>
                                    This is your default followout
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
                                    @if (auth()->check() && auth()->user()->isFollowhost())
                                        This is <a href="{{ route('coupons.index') }}">GEO Coupon</a> Followout
                                    @else
                                        This is GEO Coupon Followout
                                    @endif
                                </div>
                            @endif
                            @if (app()->isLocal())
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-database"></i>
                                    Secret Hash: <span style="font-size: 50%; vertical-align: middle;">{{ $followout->hash }}</span>
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
                                    <strong>Virtual Location:</strong> <a target="_blank" href="{{ route('followouts.virtual-address.go', ['followout' => $followout->id]) }}" title="{{ $followout->virtual_address }}">{{ get_domain($followout->virtual_address) }}</a>
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
                        @forelse ($followout->accepted_followees as $followee)
                            <div class="ProfileLinkedItems__item">
                                <div class="ProfileLinkedItems__name-and-picture clearfix">
                                    <a href="{{ $followee->user->url() }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $followee->user->avatarURL() }})"></a>
                                    <div class="ProfileLinkedItems__name">{{ $followee->user->name }}</div>
                                </div>
                                <div class="ProfileLinkedItems__description">
                                    {!! nl2br(e($followee->user->about)) !!}
                                </div>
                            </div>
                        @empty
                            <div class="text-muted text-center">
                                There are no Followees yet.
                            </div>
                        @endforelse
                    </div>
                    @if (auth()->check() && auth()->user()->id === $followout->author->id)
                        @if ($pendingFollowees->count() > 0)
                            {{-- Pending Followees --}}
                            <div class="ProfileLinkedItems">
                                <div class="ProfileLinkedItems__title">Pending Followees</div>
                                @foreach ($pendingFollowees as $followee)
                                    <div class="ProfileLinkedItems__item">
                                        <div class="ProfileLinkedItems__name-and-picture clearfix">
                                            <a href="{{ $followee->user->url() }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $followee->user->avatarURL() }})"></a>
                                            <div class="ProfileLinkedItems__name">{{ $followee->user->name }}</div>
                                        </div>
                                        <div class="ProfileLinkedItems__description">
                                            {!! nl2br(e($followee->user->about)) !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($pendingApprovalFollowees->count() > 0)
                            {{-- Present Followout Requests --}}
                            <div class="ProfileLinkedItems">
                                <div class="ProfileLinkedItems__title">Present Followout Requests</div>
                                @foreach ($pendingApprovalFollowees as $followee)
                                    <div class="ProfileLinkedItems__item">
                                        <div class="ProfileLinkedItems__name-and-picture clearfix">
                                            <a href="{{ $followee->user->url() }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $followee->user->avatarURL() }})"></a>
                                            <div class="ProfileLinkedItems__name">{{ $followee->user->name }}</div>
                                        </div>
                                        <div class="ProfileLinkedItems__description">
                                            {!! nl2br(e($followee->user->about)) !!}
                                        </div>
                                        <div class="ProfileLinkedItems__buttons ButtonRow">
                                            <a href="{{ route('followouts.present-request.accept', ['followout' => $followout->id, 'user' => $followee->user->id]) }}" class="Button Button--sm Button--danger">Accept</a>
                                            <a href="{{ route('followouts.present-request.decline', ['followout' => $followout->id, 'user' => $followee->user->id]) }}" class="Button Button--sm Button--primary">Decline</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if (session()->has('SHOW_FOLLOWOUT_REPOSTED_TUTORIAL'))
    @push('modals')
        @include('includes.modals.followout-reposted')
        <script> $(function() { $('#followout-reposted-modal').modal('show'); }); </script>
    @endpush
@endif

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
