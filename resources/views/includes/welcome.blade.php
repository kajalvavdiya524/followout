@php
    $showLandingCTA = Route::currentRouteName() === 'welcome' && (auth()->guest() || (auth()->user()->isFollowhost() && !auth()->user()->subscribed()));
@endphp

{{-- CTA --}}
@if ($showLandingCTA)
    @include('includes.landing-subscription')
@endif

{{-- Followouts --}}
<div class="Section Section--padding-sm {{ $showLandingCTA ? 'Section--bg-gray' : '' }}" style="{{ $showLandingCTA || isset($data['exception']) ? '' : 'margin-top: -15px;' }}">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="WelcomeHeadline clearfix">
                    <div class="WelcomeHeadline__title">
                        Latest <span class="text-primary text-semibold">Followout Activities, Announcements, Experiences</span>
                    </div>
                    <div class="WelcomeHeadline__controls">
                        @if ($data['followouts']->count() > 0)
                            <span id="prevFollowoutOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-left"></i></span>
                            <span id="nextFollowoutOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-right"></i></span>
                        @endif
                        <a href="{{ route('followouts.index') }}" class="WelcomeHeadline__control WelcomeHeadline__control--text">View all</a>
                    </div>
                </div>
                @if ($data['followouts']->count() > 0)
                    <div id="followoutsOwl" class="owl-carousel">
                        @foreach ($data['followouts'] as $followout)
                            <div class="UserCard">
                                <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}">
                                    <img class="UserCard__flyer" src="{{ $followout->flyerURL() }}">
                                </a>
                                <div class="UserCard__name">
                                    {{ $followout->title }}
                                </div>
                                <div class="UserCard__misc">
                                    {{ $followout->isOngoing() ? 'Started' : 'Starts' }} {{ $followout->starts_at->tz(session_tz())->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted" style="padding: 30px 0;">
                        Nothing here yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- GEO Coupon Followouts --}}
<div class="Section Section--padding-sm {{ $showLandingCTA ? '' : 'Section--bg-gray' }}">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="WelcomeHeadline clearfix">
                    <div class="WelcomeHeadline__title">
                        Latest <span class="text-primary text-semibold">Followout Coupons, Deals, Offers</span>
                    </div>
                    <div class="WelcomeHeadline__controls">
                        @if ($data['geo_coupon_followouts']->count() > 0)
                            <span id="prevFollowoutOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-left"></i></span>
                            <span id="nextFollowoutOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-right"></i></span>
                        @endif
                        <a href="{{ route('followouts.index') }}" class="WelcomeHeadline__control WelcomeHeadline__control--text">View all</a>
                    </div>
                </div>
                @if ($data['geo_coupon_followouts']->count() > 0)
                    <div id="geoFollowoutsOwl" class="owl-carousel">
                        @foreach ($data['geo_coupon_followouts'] as $followout)
                            <div class="UserCard">
                                <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}">
                                    <img class="UserCard__flyer" src="{{ $followout->flyerURL() }}">
                                </a>
                                <div class="UserCard__name">
                                    {{ $followout->title }}
                                </div>
                                <div class="UserCard__misc">
                                    {{ $followout->isOngoing() ? 'Started' : 'Starts' }} {{ $followout->starts_at->tz(session_tz())->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted" style="padding: 30px 0;">
                        Nothing here yet.
                        <br>
                        <br>
                        @guest
                            Sign in and subscribe to your favorite Followhosts to view GEO Coupon Followouts.
                        @else
                            Subscribe to your favorite Followhosts to view GEO Coupon Followouts.
                        @endguest
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Followees --}}
{{--
<div class="Section Section--padding-sm {{ $showLandingCTA ? 'Section--bg-gray' : '' }}">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="WelcomeHeadline clearfix">
                    <div class="WelcomeHeadline__title">
                        Latest <span class="text-primary text-semibold">Followee Employees, Influencers, Promoters, Talent</span>
                    </div>
                    <div class="WelcomeHeadline__controls">
                        @if ($data['followees']->count() > 0)
                            <span id="prevFolloweeOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-left"></i></span>
                            <span id="nextFolloweeOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-right"></i></span>
                        @endif
                        <a href="{{ route('users.index.followees') }}" class="WelcomeHeadline__control WelcomeHeadline__control--text">View all</a>
                    </div>
                </div>
                @if ($data['followees']->count() > 0)
                    <div id="followeesOwl" class="owl-carousel">
                        @foreach ($data['followees'] as $user)
                            <div class="UserCard">
                                <a href="{{ route('users.show', ['user' => $user->id]) }}">
                                    <img class="UserCard__avatar" src="{{ $user->avatarURL() }}">
                                </a>
                                <div class="UserCard__name">
                                    {{ $user->name }}
                                </div>
                                <div class="UserCard__misc">
                                    @if ($user->city || $user->country)
                                        {{ $user->city ? ($user->city && $user->country ? $user->city.',' : $user->city) : null }} {{ $user->country ? $user->country->name : null }}
                                    @else
                                        Unknown location
                                    @endif
                                </div>
                                <div class="UserCard__buttons">
                                    @if (auth()->check() && auth()->user()->following($user->id))
                                        <a href="{{ route('users.unsubscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton FollowButton--following"></a>
                                    @else
                                        @if (auth()->check() && auth()->user()->id === $user->id)
                                            <a href="{{ route('users.subscribe', ['user' => $user->id]) }}" class="Button Button--block Button--disabled FollowButton FollowButton--following"></a>
                                        @else
                                            <a href="{{ route('users.subscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton"></a>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted" style="padding: 30px 0;">
                        No one's here yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
--}}

{{-- Followhosts --}}
{{--
<div class="Section Section--last Section--padding-sm {{ $showLandingCTA ? '' : 'Section--bg-gray' }}">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="WelcomeHeadline clearfix">
                    <div class="WelcomeHeadline__title">
                        Latest <span class="text-primary text-semibold">Followhost Directory Brands, Businesses, Venues</span>
                    </div>
                    <div class="WelcomeHeadline__controls">
                        @if ($data['followhosts']->count() > 0)
                            <span id="prevFollowhostOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-left"></i></span>
                            <span id="nextFollowhostOwl" class="WelcomeHeadline__control"><i class="fas fa-fw fa-angle-right"></i></span>
                        @endif
                        <a href="{{ route('users.index.followhosts') }}" class="WelcomeHeadline__control WelcomeHeadline__control--text">View all</a>
                    </div>
                </div>
                @if ($data['followhosts']->count() > 0)
                    <div id="followhostsOwl" class="owl-carousel">
                        @foreach ($data['followhosts'] as $user)
                            <div class="UserCard">
                                <a href="{{ route('users.show', ['user' => $user->id]) }}">
                                    <img class="UserCard__avatar" src="{{ $user->avatarURL() }}">
                                </a>
                                <div class="UserCard__name">
                                    {{ $user->name }}
                                </div>
                                <div class="UserCard__misc">
                                    @if ($user->city || $user->country)
                                        {{ $user->city ? ($user->city && $user->country ? $user->city.',' : $user->city) : null }} {{ $user->country ? $user->country->name : null }}
                                    @else
                                        Unknown location
                                    @endif
                                </div>
                                @if ($user->isFollowhost() && $user->subscribed())
                                    <div class="UserCard__buttons">
                                        <a href="{{ route('followouts.create-manually', ['followhost' => $user->id]) }}" class="Button Button--block Button--primary" title="Create Followout with location of this user">
                                            Create {{ $user->name }} Followout
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted" style="padding: 30px 0;">
                        No one's here yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
--}}

@push('scripts-footer')
    <script>
        $(function() {
            var followoutsOwl = $('#followoutsOwl');

            followoutsOwl.owlCarousel({
                autoplay: true,
                autoplayTimeout: 7500,
                autoplayHoverPause: true,
                loop: false,
                dots: false,
                margin: 20,
                responsiveClass: true,
                responsive: {
                    0: { items: 1, nav: false },
                    480: { items: 2, nav: false },
                    768: { items: 4, nav: false },
                    1200: { items: 4, nav: false },
                }
            });

            $('#prevFollowoutOwl').on('click', function(event){ event.preventDefault(); followoutsOwl.trigger('prev.owl.carousel'); });
            $('#nextFollowoutOwl').on('click', function(event){ event.preventDefault(); followoutsOwl.trigger('next.owl.carousel'); });

            var geoFollowoutsOwl = $('#geoFollowoutsOwl');

            geoFollowoutsOwl.owlCarousel({
                autoplay: true,
                autoplayTimeout: 7500,
                autoplayHoverPause: true,
                loop: false,
                dots: false,
                margin: 20,
                responsiveClass: true,
                responsive: {
                    0: { items: 1, nav: false },
                    480: { items: 2, nav: false },
                    768: { items: 4, nav: false },
                    1200: { items: 4, nav: false },
                }
            });

            $('#prevGeoFollowoutOwl').on('click', function(event){ event.preventDefault(); followoutsOwl.trigger('prev.owl.carousel'); });
            $('#nextGeoFollowoutOwl').on('click', function(event){ event.preventDefault(); followoutsOwl.trigger('next.owl.carousel'); });

            /*
            var followeesOwl = $('#followeesOwl');

            followeesOwl.owlCarousel({
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                loop: false,
                dots: false,
                margin: 20,
                responsiveClass: true,
                responsive: {
                    0: { items: 1, nav: false },
                    480: { items: 2, nav: false },
                    768: { items: 4, nav: false },
                    1200: { items: 4, nav: false },
                }
            });

            $('#prevFolloweeOwl').on('click', function(event){ event.preventDefault(); followeesOwl.trigger('prev.owl.carousel'); });
            $('#nextFolloweeOwl').on('click', function(event){ event.preventDefault(); followeesOwl.trigger('next.owl.carousel'); });

            var followhostsOwl = $('#followhostsOwl');

            followhostsOwl.owlCarousel({
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true,
                loop: false,
                dots: false,
                margin: 20,
                responsiveClass: true,
                responsive: {
                    0: { items: 1, nav: false },
                    480: { items: 2, nav: false },
                    768: { items: 4, nav: false },
                    1200: { items: 4, nav: false },
                }
            });

            $('#prevFollowhostOwl').on('click', function(event){ event.preventDefault(); followhostsOwl.trigger('prev.owl.carousel'); });
            $('#nextFollowhostOwl').on('click', function(event){ event.preventDefault(); followhostsOwl.trigger('next.owl.carousel'); });
            */
        });
    </script>
@endpush
