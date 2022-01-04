<div class="Section Section--no-padding" style="margin-top: -15px;">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="LandingHero">
                    <div class="LandingHero__heading">
                        The Social Interaction Platform
                        <br>
                        To Promote Your Brand or Business
                    </div>
                    <div class="LandingHero__checklist">
                        <div class="LandingHero__checklist-item">
                            Activities
                        </div>
                        <div class="LandingHero__checklist-item">
                            Announcements
                        </div>
                        <div class="LandingHero__checklist-item">
                            Experiences
                        </div>
                    </div>

                    <div class="LandingHero__guide">
                        <div class="LandingHero__guide-heading">
                            How it works
                        </div>
                        <div class="LandingHero__guide-item">
                            <div class="LandingHero__guide-item-heading">
                                Create
                            </div>
                            <div class="LandingHero__guide-item-description">
                                Start a new Followout or use the default created for you.
                            </div>
                        </div>
                        <div class="LandingHero__guide-item">
                            <div class="LandingHero__guide-item-heading">
                                Promote
                            </div>
                            <div class="LandingHero__guide-item-description">
                                Use platform to share your Followouts to your social network and invite presenters such as staff, promoters, or influencers to do the same.
                            </div>
                        </div>
                        <div class="LandingHero__guide-item">
                            <div class="LandingHero__guide-item-heading">
                                Grow
                            </div>
                            <div class="LandingHero__guide-item-description">
                                Your public Followout displays on website and iOS mobile platform and is also displayed to your subscribers and the subscribers of your presenters.
                            </div>
                        </div>
                    </div>

                    @if (auth()->guest() || auth()->user()->isFollowhost())
                        <div class="LandingHero__buttons ButtonGroup ButtonGroup--center">
                            @guest
                                {{-- <a href="{{ route('register', ['plan' => 'basic']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'basic']) }}');">
                                    Order Now (Freelancers)
                                </a> --}}
                                <a href="{{ route('register', ['plan' => 'monthly']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'monthly']) }}');">
                                    Order Now (one month)
                                </a>
                                <a href="{{ route('register', ['plan' => 'annual']) }}" class="Button Button--primary" onclick="return gtag_report_conversion_and_redirect('{{ route('register', ['plan' => 'annual']) }}');">
                                    Order Now (one year)
                                </a>
                            @else
                                {{-- <a href="{{ route('cart.add', ['product' => $appData['basicSubscription']->id]) }}" class="Button Button--primary">
                                    Order Now (Freelancers)
                                </a> --}}
                                <a href="{{ route('cart.add', ['product' => $appData['monthlySubscription']->id]) }}" class="Button Button--primary">
                                    Order Now (one month)
                                </a>
                                <a href="{{ route('cart.add', ['product' => $appData['yearlySubscription']->id]) }}" class="Button Button--primary">
                                    Order Now (one year)
                                </a>
                            @endguest
                        </div>

                        <div class="LandingHero__buttons LandingHero__buttons--offer-text" style="display: flex; flex-direction: row;">
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

                        <br>

                        <div style="margin: auto; width: 100%; max-width: 120px;">
                            <img src="/img/price-intro.jpg" alt="Special introductory price" class="img-responsive">
                        </div>
                        <br>
                        <br>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('includes.gtag-report-conversion-guest-subscription')
