@if (Route::currentRouteName() === 'pricing')
    <style>
        #testimonials {
            display: none;
            width: 100%;
            margin: auto;
            margin-bottom: 15px;
        }
        #testimonials img {
            display: block;
            margin: auto;
            max-height: 160px;
        }
        #banner {
            display: none;
            position: absolute;
            width: 160px;
            right: 0px;
            top: 50px;
        }
        @media screen and (min-width: 1500px) {
            #testimonials {
                width: calc(100% - 175px);
                margin-bottom: 30px;
                margin-left: 0;
            }
            #testimonials img {
                margin-right: 0;
            }
            #banner {
                display: block;
            }
        }
    </style>
@endif

<div class="Section Section--no-padding" style="{{ Route::currentRouteName() === 'welcome' ? 'margin-top: -15px;' : '' }}">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="LandingHero">
                    @if (Route::currentRouteName() === 'pricing')
                        <div id="testimonials">
                            <img src="/img/testimonials.png" alt="Testimonials" class="img-responsive">
                        </div>
                        <div id="banner">
                            <img src="/img/price-intro.jpg" alt="Special introductory price" class="img-responsive">
                        </div>
                    @endif
                    <div class="LandingHero__heading">
                        {{-- The Social Interaction Platform<br>To Promote Your Brand or Business --}}
                        OUR CUSTOMERS WANT:
                    </div>
                    <marquee class="LandingHero__ticker">
                        • TO GET MORE CUSTOMERS THROUGH THEIR DOORS
                        • TO KEEP THEM COMING BACK ON A REGULAR BASIS
                        • AND TO DO IT AS EASILY AND INEXPENSIVELY AS POSSIBLE
                        • THE SOCIAL INTERACTION PLATFORM TO PROMOTE YOUR BRAND OR BUSINESS
                    </marquee>
                    @if (optional($data['landing_hero'])->gallery_video_url)
                        <div class="LandingHero__gallery LandingHero__gallery--video-only">
                            <div class="LandingHero__gallery-item">
                                <video src="{{ $data['landing_hero']->gallery_video_url }}" autoplay controls></video>
                            </div>
                        </div>
                    @else
                        {{-- <div class="LandingHero__checklist">
                            <div class="LandingHero__checklist-item">
                                Activities
                            </div>
                            <div class="LandingHero__checklist-item">
                                Restaurants
                            </div>
                            <div class="LandingHero__checklist-item hidden-xs">
                                Invite
                            </div>
                            <div class="LandingHero__checklist-item">
                                Announcements
                            </div>
                            <div class="LandingHero__checklist-item">
                                Stores
                            </div>
                            <div class="LandingHero__checklist-item hidden-xs">
                                Present
                            </div>
                            <div class="LandingHero__checklist-item">
                                Experiences
                            </div>
                            <div class="LandingHero__checklist-item">
                                Websites
                            </div>
                            <div class="LandingHero__checklist-item visible-xs">
                                Present
                            </div>
                            <div class="LandingHero__checklist-item visible-xs">
                                Invite
                            </div>
                            <div class="LandingHero__checklist-item">
                                Promote
                            </div>
                        </div> --}}

                        <div class="LandingHero__gallery">
                            <div class="LandingHero__gallery-item hidden-xs">
                                @if (optional($data['landing_hero'])->gallery_picture_1_url)
                                    <img src="{{ $data['landing_hero']->gallery_picture_1_url }}">
                                @else
                                    <img src="{{ url('/img/landing-hero-gallery-1.jpg') }}">
                                @endif
                            </div>
                            <div class="LandingHero__gallery-item">
                                @if (optional($data['landing_hero'])->gallery_video_url)
                                    <video src="{{ $data['landing_hero']->gallery_video_url }}" autoplay controls></video>
                                @else
                                    @if (optional($data['landing_hero'])->gallery_picture_2_url)
                                        <img src="{{ $data['landing_hero']->gallery_picture_2_url }}">
                                    @else
                                        <img src="{{ url('/img/landing-hero-gallery-2.jpg') }}">
                                    @endif
                                @endif
                            </div>
                            <div class="LandingHero__gallery-item hidden-xs">
                                @if (optional($data['landing_hero'])->gallery_picture_3_url)
                                    <img src="{{ $data['landing_hero']->gallery_picture_3_url }}">
                                @else
                                    <img src="{{ url('/img/landing-hero-gallery-3.jpg') }}">
                                @endif
                            </div>
                        </div>
                    @endif

                    @if (Route::currentRouteName() === 'pricing')
                        @if (auth()->guest() || auth()->user()->isFollowhost())
                            <div class="LandingHero__buttons ButtonGroup ButtonGroup--center">
                                @guest
                                    <a href="{{ route('register') }}" class="Button Button--primary">
                                        Free
                                    </a>
                                    @if (app()->environment('production'))
                                        {{-- <a href="{{ route('register', ['plan' => 'basic']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'basic']) }}');">
                                            Freelancers
                                        </a> --}}
                                        <a href="{{ route('register', ['plan' => 'monthly']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'monthly']) }}');">
                                            One month
                                        </a>
                                        <a href="{{ route('register', ['plan' => 'annual']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'annual']) }}');">
                                            One year
                                        </a>
                                    @else
                                        {{-- <a href="{{ route('register', ['plan' => 'basic']) }}" class="Button Button--primary">
                                            Freelancers
                                        </a> --}}
                                        <a href="{{ route('register', ['plan' => 'monthly']) }}" class="Button Button--primary">
                                            One month
                                        </a>
                                        <a href="{{ route('register', ['plan' => 'annual']) }}" class="Button Button--primary">
                                            One year
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('subscribe.free') }}" class="Button Button--primary">
                                        Free
                                    </a>
                                    {{-- <a href="{{ route('cart.add', ['product' => $appData['basicSubscription']->id]) }}" class="Button Button--primary">
                                        Freelancers
                                    </a> --}}
                                    <a href="{{ route('cart.add', ['product' => $appData['monthlySubscription']->id]) }}" class="Button Button--primary">
                                        One month
                                    </a>
                                    <a href="{{ route('cart.add', ['product' => $appData['yearlySubscription']->id]) }}" class="Button Button--primary">
                                        One year
                                    </a>
                                @endguest
                            </div>

                            <div class="LandingHero__buttons LandingHero__buttons--offer-text">
                                <div class="text-center" style="flex: 1;">
                                    <span class="text-bold">Try it free!</span>
                                    <br>
                                    <span class="text-muted">(Unlimited Followouts)</span>
                                    <br>
                                    <span class="text-muted">(Promote to Followout community)</span>
                                </div>
                                <div class="text-center" style="flex: 1;">
                                    <span class="text-bold">
                                        ${{ number_format($appData['basicSubscription']->price, 2) }} / non-recurring
                                    </span>
                                    <br>
                                    <span class="text-muted">(Unlimited)</span>
                                    <br>
                                    <span class="text-muted">(Promote to everyone)</span>
                                </div>
                                <div class="text-center" style="flex: 1;">
                                    <span class="text-bold">
                                        ${{ number_format($appData['monthlySubscription']->price, 2) }} / mo.
                                    </span>
                                    <br>
                                    <span class="text-muted">(Unlimited)</span>
                                    <br>
                                    <span class="text-muted">(Promote to everyone)</span>
                                    <br>
                                    <br>
                                    Great for starting out!
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

                            <br>

                            <div class="LandingHero__subheading LandingHero__subheading--with-pricing">
                                Post Your Images, GIFS, Videos, Coupons, Deals, or Offers
                                <br>
                                And Let Everyone Know Where To Go And What To Do
                            </div>
                        @endif
                    @else
                        <div class="LandingHero__subheading">
                            Post Your Images, GIFS, Videos, Coupons, Deals, or Offers
                            <br>
                            And Let Everyone Know Where To Go And What To Do
                        </div>
                        <div class="LandingHero__buttons ButtonGroup ButtonGroup--center">
                            <a href="{{ route('university') }}" class="Button Button--primary">
                                Gain New Subscribers Instantly
                            </a>
                        </div>
                    @endif
                </div>
                <div class="LandingHero__mockup LandingHero__mockup--left">
                    @if (optional($data['landing_hero'])->screenshot_1_video_url)
                        <video src="{{ $data['landing_hero']->screenshot_1_video_url }}" controls muted loop></video>
                    @endif
                    <img src="{{ url('/img/landing-hero-screenshot-1.png') }}">
                </div>

                <div class="LandingHero__mockup LandingHero__mockup--right">
                    @if (optional($data['landing_hero'])->screenshot_2_video_url)
                        <video src="{{ $data['landing_hero']->screenshot_2_video_url }}" controls muted loop></video>
                    @endif
                    <img src="{{ url('/img/landing-hero-screenshot-2.png') }}">
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts-footer')
    @if (optional($data['landing_hero'])->screenshot_1_video_url)
        <script>
            if ($(window).width() >= 1200) {
                var videoLeft = $('.LandingHero__mockup--left video')[0];
                videoLeft.play();
            }
        </script>
    @endif

    @if (optional($data['landing_hero'])->screenshot_2_video_url)
        <script>
            if ($(window).width() >= 1200) {
                var videoRight = $('.LandingHero__mockup--right video')[0];
                videoRight.play();
            }
        </script>
    @endif
@endpush

@include('includes.gtag-report-conversion-guest-subscription')
