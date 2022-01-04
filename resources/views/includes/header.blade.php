<div class="Header">
    <div class="container">
        <div class="row">
            <div class="Header__wrap">
                <a class="Header__logo" href="/"></a>

                <div class="Header__nav-toggle" data-toggle="collapse" data-target="#header-nav">
                    <i class="fas fa-fw fa-bars"></i>
                </div>

                @if (auth()->guest())
                    <div id="header-nav-cta" class="Header__nav Header__nav--cta">
                        <a href="{{ route('login') }}" class="Header__nav-item {{ Route::is('login') ? 'Header__nav-item--active' : null }}">Login</a>
                        <a href="{{ route('university') }}" class="Header__nav-item {{ Route::is('university') ? 'Header__nav-item--active' : null }}">List My Business</a>
                        {{-- <a href="{{ route('register') }}" class="Header__nav-item {{ Route::is('register') ? 'Header__nav-item--active' : null }}">Sign up</a> --}}
                    </div>
                @endif
                <div id="header-nav" class="Header__nav collapse">
                    @include('includes.search.header')

                    @if (auth()->check() && !auth()->user()->isUnregistered() && auth()->user()->isActivated())
                        <a href="{{ route('notifications.index') }}" class="Header__nav-item">
                            <i class="fas fa-fw fa-bell">
                                @if (auth()->user()->hasUnreadNotifications())
                                    <span class="has-unread-notifications-icon"></span>
                                @endif
                            </i>
                        </a>
                        {{-- <a href="{{ route('messages.chats') }}" class="Header__nav-item">
                            <i class="fas fa-fw fa-comments">
                                @if (auth()->user()->hasUnreadMessages())
                                    <span class="has-unread-messages-icon"></span>
                                @endif
                            </i>
                        </a> --}}
                    @endunless

                    @unless (auth()->check() && (auth()->user()->isUnregistered() || !auth()->user()->isActivated()))
                        <a href="{{ route('followouts.index') }}" class="Header__nav-item">Followouts</a>
                        <a href="{{ route('users.index.followees') }}" class="Header__nav-item">Followees</a>
                        @auth
                            <a href="javascript:void(0);" class="Header__nav-item Header__nav-item--star" data-toggle="modal" data-target="#north-star-modal"></a>
                            @push('modals')
                                @include('includes.modals.north-star')
                            @endpush
                        @endauth
                        <a href="{{ route('users.index.followhosts') }}" class="Header__nav-item">Followhosts</a>
                    @endunless
                    @if (auth()->guest())
                        <a href="{{ route('login') }}" class="Header__nav-item hidden-sm hidden-md hidden-lg {{ Route::is('login') ? 'Header__nav-item--active' : null }}">Login</a>
                        <a href="{{ route('university') }}" class="Header__nav-item hidden-sm hidden-md hidden-lg {{ Route::is('university') ? 'Header__nav-item--active' : null }}">List My Business</a>
                        {{-- <a href="{{ route('register') }}" class="Header__nav-item hidden-sm hidden-md hidden-lg {{ Route::is('register') ? 'Header__nav-item--active' : null }}">Sign up</a> --}}
                    @else
                        <li class="Header__nav-item Header__nav-item--dropdown  Header__nav-item--user-dropdown dropdown">
                            <a href="javascript:void(0);" id="header-dropdown" class="Header__nav-item Header__nav-item--dropdown-toggle dropdown-toggle" data-toggle="dropdown">
                                {{ auth()->user()->name }} <span class="caret"></span>
                            </a>
                            <ul class="Header__dropdown Header__dropdown--user dropdown-menu" aria-labelledby="header-dropdown">
                                @unless (auth()->check() && (auth()->user()->isUnregistered() || !auth()->user()->isActivated()))
                                    <li>
                                        <a href="{{ route('users.show', ['user' => auth()->user()->id]) }}">
                                            <i class="fas fa-fw fa-user-circle"></i>
                                            My profile
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ action('UsersController@edit', ['user' => auth()->user()->id]) }}">
                                            <i class="fas fa-fw fa-pencil-alt"></i>
                                            Enhance profile
                                            @if (auth()->user()->isFollowhost() && auth()->user()->isMissingProfileInfo())
                                                <strong class="pull-right text-danger">profile incomplete</strong>
                                            @endif
                                        </a>
                                    </li>

                                    <li>
                                        <a href="{{ route('coupons.index') }}">
                                            <i class="fas fa-fw fa-ticket-alt"></i>
                                            Manage GEO Coupons, Deals, Offers
                                        </a>
                                    </li>

                                    @if (auth()->user()->isFollowhost())
                                        <li>
                                            <a href="{{ route('reward_programs.index') }}">
                                                <i class="fas fa-fw fa-award"></i>
                                                Manage Reward Programs
                                            </a>
                                        </li>
                                    @endif

                                    <li>
                                        <a href="{{ route('reward_program_jobs.index') }}">
                                            <i class="fas fa-fw fa-medal"></i>
                                            Present Followouts
                                        </a>
                                    </li>

                                    <li role="separator" class="divider"></li>

                                    @if (auth()->user()->subscribed())
                                        <li>
                                            <a target="_blank" href="{{ config('followouts.followout_ios_app_url') }}">
                                                <i class="fab fa-fw fa-apple"></i>
                                                FollowOut on the App Store
                                            </a>
                                        </li>
                                        <li role="separator" class="divider"></li>
                                    @endif

                                    @if (auth()->user()->isFollowhost() && !auth()->user()->subscribed())
                                        <li>
                                            <a href="{{ route('pricing') }}">
                                                <i class="fas fa-fw fa-star"></i>
                                                Subscribe to Followouts Pro
                                            </a>
                                        </li>

                                        <li role="separator" class="divider"></li>
                                    @endif

                                    <li>
                                        <a href="{{ route('cart') }}">
                                            <i class="fas fa-fw fa-shopping-cart"></i>
                                            Shopping Cart
                                        </a>
                                    </li>

                                    @if (auth()->user()->isAdmin())
                                        @php
                                            $deletedUsersCount = \App\User::toBeDeleted()->count();
                                        @endphp

                                        <li role="separator" class="divider"></li>

                                        <li>
                                            <a href="{{ route('payouts.index') }}">
                                                <i class="fas fa-fw fa-dollar-sign"></i>
                                                Payouts
                                            </a>
                                        </li>

                                        @if ($deletedUsersCount > 0)
                                            <li>
                                                <a href="{{ route('users.manage.index') }}">
                                                    <i class="fas fa-fw fa-users"></i>
                                                    Manage users <strong class="pull-right">{{ $deletedUsersCount }} new</strong>
                                                </a>
                                            </li>
                                        @else
                                            <li>
                                                <a href="{{ route('users.manage.index') }}">
                                                    <i class="fas fa-fw fa-users"></i>
                                                    Manage users
                                                </a>
                                            </li>
                                        @endif

                                        <li>
                                            <a href="{{ route('products.index') }}">
                                                <i class="fas fa-fw fa-pencil-alt"></i>
                                                Products &amp; promo codes
                                            </a>
                                        </li>

                                        <li>
                                            <a href="{{ route('sales-reps.index') }}">
                                                <i class="fas fa-fw fa-pencil-alt"></i>
                                                Sales representatives
                                            </a>
                                        </li>

                                        <li>
                                            <a href="{{ route('app.static-content.edit') }}">
                                                <i class="fas fa-fw fa-pencil-alt"></i>
                                                Static content
                                            </a>
                                        </li>

                                        <li>
                                            <a href="{{ route('app.experience-categories.index') }}">
                                                <i class="fas fa-fw fa-pencil-alt"></i>
                                                Experience categories
                                            </a>
                                        </li>

                                        <li>
                                            <a href="{{ route('app.deploy') }}">
                                                <i class="fas fa-fw fa-arrow-circle-up"></i>
                                                Update application
                                            </a>
                                        </li>
                                    @endif

                                    <li role="separator" class="divider"></li>

                                    <li>
                                        <a href="{{ route('settings.account') }}">
                                            <i class="fas fa-fw fa-cog"></i>
                                            Settings
                                        </a>
                                    </li>

                                    <li role="separator" class="divider"></li>
                                @endunless

                                <li>
                                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-fw fa-power-off"></i>
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endif
                </div>

                @if (auth()->check())
                    <a href="{{ route('reward_program_jobs.index') }}" class="Header__nav-item Header__nav-item--jobs-link">
                        Present Followouts
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
