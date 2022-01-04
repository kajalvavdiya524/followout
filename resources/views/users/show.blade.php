@extends('layouts.app')

@section('page-title', $user->name)

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="ProfilePicture">
                        <img class="ProfilePicture__picture" src="{{ $user->avatarURL() }}" />
                    </div>
                    @if ($user->avatars()->count() >= 2)
                        <div class="ProfilePictureThumbs {{ 'ProfilePictureThumbs--'.$user->avatars()->count() }}">
                            @foreach ($user->avatars as $key => $avatar)
                                <img class="ProfilePictureThumbs__thumb {{ $key == 0 ? 'active' : ''}}" src="{{ $user->avatarURL($key) }}" />
                            @endforeach
                        </div>
                    @endif
                    @if (auth()->check() && (auth()->user()->id === $user->id || auth()->user()->isAdmin()))
                        <div class="ProfileShareButtons">
                            <div class="sharethis-inline-share-buttons"
                                data-url="{{ convertToHttpScheme($user->url()) }}"
                                data-title="{{ $user->name }}"
                                data-image="{{ convertToHttpScheme($user->avatarURL()) }}"
                                data-description="{{ $user->about }}"
                            ></div>
                        </div>
                    @endif
                    @if (auth()->check() && auth()->user()->id !== $user->id)
                        <div class="ProfileButtonsWrap">
                            @if (auth()->user()->following($user->id))
                                <a href="{{ route('users.unsubscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton FollowButton--following">
                                    <i class="fas fa-fw fa-check"></i>
                                </a>
                            @else
                                <a href="{{ route('users.subscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton">
                                    <i class="fas fa-fw fa-plus"></i>
                                </a>
                            @endif
                            @if ($user->following(auth()->user()->id))
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#invite-attendee-modal">
                                    <i class="fas fa-fw fa-users"></i>
                                    Invite to Attend
                                </div>
                                @push('modals')
                                    @include('includes.modals.invite-attendee')
                                @endpush
                            @endif
                            @if (Gate::allows('invite-followee', $user))
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#invite-followee-modal">
                                    <i class="far fa-fw fa-envelope"></i>
                                    Invite Followee to Present
                                </div>
                                @push('modals')
                                    @include('includes.modals.invite-followee')
                                @endpush
                            @elseif (auth()->user()->isFollowhost() && !auth()->user()->subscribed())
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                    <i class="far fa-fw fa-envelope"></i>
                                    Invite Followee to Present
                                </div>
                            @endif
                            @if (Gate::allows('introduce-yourself', $user))
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#followee-intro-modal">
                                    <i class="fas fa-fw fa-street-view"></i>
                                    Introduce Myself
                                </div>
                                @push('modals')
                                    @include('includes.modals.followee-intro')
                                @endpush
                            @endif
                            @if (auth()->user()->isAdmin())
                                @if ($user->isFollowhost())
                                    @if ($user->subscription === null || !$user->subscription->isChargebeeSubscription())
                                        <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#manage-subscription-modal">
                                            <i class="far fa-fw fa-credit-card"></i>
                                            Manage subscription
                                        </div>
                                        @push('modals')
                                            @include('includes.modals.manage-subscription')
                                        @endpush
                                    @endif
                                @endif
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#sales-rep-modal">
                                    <i class="fas fa-fw fa-user-secret"></i>
                                    Sales Representative
                                </div>
                                @push('modals')
                                    @include('includes.modals.sales-rep')
                                @endpush
                                @unless ($user->isFriend())
                                    <a href="{{ route('users.change-role', ['user' => $user->id, 'role' => 'friend']) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-user"></i>
                                        Switch to Friend
                                    </a>
                                @endunless
                                @unless ($user->isFollowee())
                                    <a href="{{ route('users.change-role', ['user' => $user->id, 'role' => 'followee']) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-user"></i>
                                        Switch to Followee
                                    </a>
                                @endunless
                                @unless ($user->isFollowhost())
                                    <a href="{{ route('users.change-role', ['user' => $user->id, 'role' => 'followhost']) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-user"></i>
                                        Switch to Followhost
                                    </a>
                                @endunless
                                @if ($user->isFollowhost() && $user->subscribed())
                                    <a href="{{ route('users.manage.update-default-followout', ['user' => $user->id]) }}" class="Button Button--block Button--danger">
                                        <i class="fas fa-fw fa-sync-alt"></i>
                                        Sync default Followout
                                    </a>
                                @endif
                                <a href="{{ route('login-as-user', ['user' => $user->id]) }}" class="Button Button--block Button--danger">
                                    <i class="fas fa-fw fa-sign-in-alt"></i>
                                    Login as User
                                </a>
                                <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#delete-user-modal">
                                    @if ($user->markedForDeletion())
                                        <i class="fas fa-fw fa-check-circle"></i>
                                        Approve Account Deactivation Request
                                    @else
                                        <i class="far fa-fw fa-trash-alt"></i>
                                        Deactivate User
                                    @endif
                                </div>
                                @if ($user->markedForDeletion())
                                    <a href="{{ route('users.manage.decline-account-deletion', ['user' => $user->id]) }}" class="Button Button--block Button--danger">
                                        <i class="far fa-fw fa-times-circle"></i>
                                        Decline Account Deletion Request
                                    </a>
                                @endif
                                @push('modals')
                                    @include('includes.modals.delete-user')
                                @endpush
                            @endif
                        </div>
                    @endif
                </div>

                <div class="col-md-5">
                    <div class="ProfileInfo">
                        <div class="ProfileInfo__heading">{{ $user->name }}</div>

                        @unless ($user->isAdmin())
                            <div class="ProfileInfo__meta">
                                @if ($user->city || $user->country)
                                    <div class="ProfileInfo__meta-item">
                                        <i class="fas fa-fw fa-map-marker-alt"></i>
                                        {{ $user->city ? $user->city : null }}{{ $user->city && $user->country ? ',' : null }} {{ $user->country ? $user->country->name : null }}
                                        @if (auth()->check() && auth()->user()->id !== $user->id && $user->isFollowhost() && $user->subscribed())
                                            <div class="FollowhostCreateFollowoutButton">
                                                <a href="{{ route('followouts.create-manually', ['followhost' => $user->id]) }}" class="FollowhostCreateFollowoutButton__button" title="Create Followout with location of this user"></a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endunless

                        @if (app()->isLocal())
                            <div class="ProfileInfo__meta">
                                <div class="ProfileInfo__meta-item">
                                    <i class="fas fa-fw fa-database"></i>
                                    API Token: <span style="font-size: 40%; vertical-align: middle;">{{ $user->api_token }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="ProfileInfo__focus">
                            <p class="ProfileInfo__focus-item">
                                <strong>Experience:</strong> {{ implode(', ', $user->account_categories->pluck('name')->toArray()) }}
                            </p>
                            @unless ($user->isFriend() || $user->isFollowee())
                                <p class="ProfileInfo__focus-item">
                                    @if ($user->isFollowhost())
                                        <strong>Location:</strong> {{ $user->fullAddress() }}
                                    @else
                                        <strong>Location:</strong> {{ $user->shortAddress() }}
                                    @endif
                                </p>
                            @endunless
                        </div>

                        @if ($user->website)
                            <div class="ProfileInfo__meta ProfileInfo__meta--contacts">
                                <div class="ProfileInfo__meta-item ProfileInfo__meta-item--overflow-ellipsis">
                                    <i class="fas fa-fw fa-link"></i>
                                    <a target="_blank" href="{{ $user->website }}" class="ProfileInfo__meta-link">{{ $user->website }}</a>
                                </div>
                            </div>
                        @endif

                        <div class="ProfileInfo__about">
                            {!! nl2br(e($user->about)) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    @if (auth()->check() && auth()->user()->id === $user->id)
                        <div id="profile-tabs" class="Tabs Tabs--profile">
                            <a href="#subscriptions" class="Tab Tab--active">
                                <span class="Tab__name">
                                    My Subscriptions
                                </span>
                            </a>
                            <a href="#subscribers" class="Tab">
                                <span class="Tab__name">
                                    My Subscribers
                                </span>
                            </a>
                        </div>

                        <div id="subscriptions-list" class="ProfileLinkedItems ProfileLinkedItems--limited-height">
                            <p class="text-muted text-center">
                                {{ $subscriptions->count() }} {{ Str::plural('subscription', $subscriptions->count()) }}
                            </p>
                            @foreach ($subscriptions as $subscription)
                                <div class="ProfileLinkedItems__item">
                                    <div class="ProfileLinkedItems__name-and-picture clearfix">
                                        <a href="{{ $subscription->follows->url() }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $subscription->follows->avatarURL() }})"></a>
                                        <div class="ProfileLinkedItems__name">{{ $subscription->follows->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="subscribers-list" class="ProfileLinkedItems ProfileLinkedItems--limited-height" style="display: none;">
                            @if ($subscribers->count() > 0)
                                <p class="text-muted text-center">
                                    {{ $subscribers->count() }} {{ Str::plural('user', $subscribers->count()) }} subscribed to you recently
                                </p>
                            @else
                                <p class="text-muted text-center">
                                    @if (auth()->check() && auth()->user()->id === $user->id)
                                        Grow subscribers. Share your Followouts.
                                    @else
                                        0 subscribers
                                    @endif
                                </p>
                            @endif
                            @foreach ($subscribers as $subscriber)
                                <div class="ProfileLinkedItems__item">
                                    <div class="ProfileLinkedItems__name-and-picture clearfix">
                                        <a href="{{ $subscriber->subscriber->url() }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $subscriber->subscriber->avatarURL() }})"></a>
                                        <div class="ProfileLinkedItems__name">{{ $subscriber->subscriber->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div id="followout-list" class="ProfileLinkedItems ProfileLinkedItems--limited-height" style="display: none;">
                            @forelse ($followouts as $followout)
                                <div class="ProfileLinkedItems__item">
                                    <div class="ProfileLinkedItems__name-and-picture ProfileLinkedItems__name-and-picture--followout clearfix">
                                        <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}" class="ProfileLinkedItems__picture" style="background-image: url({{ $followout->flyerURL() }})"></a>
                                        <div class="ProfileLinkedItems__name">{{ $followout->title }}</div>
                                    </div>
                                    <div class="ProfileLinkedItems__description">
                                        {!! nl2br(e($followout->description)) !!}
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center">
                                    No current Followouts.
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
                @if (auth()->check() && auth()->user()->id != $user->id)
                    @if (@$user->video_url)
                    <div class="LandingHero__gallery-item show_video_url">
                        <iframe src="{{ $user->video_link }}"  height="180px" width="320px" autoplay allowfullscreen></iframe>
                    </div>
                    @endif
                @endif
            </div>
            <br>
            <div class="FollowoutsGrid">
                @forelse ($followouts as $followout)
                    <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}" class="FollowoutsGrid__item">
                        <img class="FollowoutsGrid__item-flyer" src="{{ $followout->flyerURL() }}"></img>
                        <div class="FollowoutsGrid__item-name">
                            {{ $followout->title }}
                        </div>
                    </a>
                @empty
                    @if (auth()->check() && auth()->user()->following($user->id))
                        <div class="text-muted">
                            Nothing here yet.
                        </div>
                    @else
                        @auth
                            @unless (auth()->id() === $user->getKey())
                                <div class="text-muted text-center">
                                    Subscribe to view more.
                                </div>
                            @endunless
                        @else
                            <div class="text-muted text-center">
                                Login and subscribe to view more.
                            </div>
                        @endauth
                    @endif
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts-footer')
    @if (auth()->check() && auth()->user()->id === $user->id)
        <script>
            $('#profile-tabs .Tab').on('click', function(e) {
                e.preventDefault();

                var tab = $(e.target);

                if (tab.hasClass('Tab--active')) {
                    return false;
                }

                $('#profile-tabs .Tab').removeClass('Tab--active');

                tab.addClass('Tab--active');

                if (tab.attr('href') === '#subscribers') {
                    $('#followout-list').hide();
                    $('#subscribers-list').show();
                    $('#subscriptions-list').hide();
                } else if (tab.attr('href') === '#subscriptions') {
                    $('#followout-list').hide();
                    $('#subscribers-list').hide();
                    $('#subscriptions-list').show();
                }
            });
        </script>
    @endif
@endpush
