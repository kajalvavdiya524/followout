@php
    $footerClass = 'Footer--7';
    if (auth()->check() && (auth()->user()->isFollowee() || auth()->user()->isFollowhost())) {
        $footerClass = 'Footer--5';
    }
@endphp

<div class="Footer {{ $footerClass }} {{ Route::currentRouteName() === 'messages.chats' ? 'hidden-xs hidden-sm' : '' }}">
    <div class="Footer__wrap">
        <div class="container">
            <div class="Footer__content">
                @unless (auth()->check() && ! auth()->user()->isActivated())
                    <div class="Footer__links">
                        <a href="{{ route('about') }}" class="Footer__link">About</a>
                        <a href="{{ route('about', ['#terms']) }}" class="Footer__link">Terms</a>
                        @unless (auth()->check() && (auth()->user()->isFollowee() || auth()->user()->isFollowhost()))
                            <a href="{{ route('about', ['#become_followee']) }}" class="Footer__link">Become a Followee</a>
                        @endunless
                        @unless (auth()->check() && (auth()->user()->isFollowee() || auth()->user()->isFollowhost()))
                            <a href="{{ route('about', ['#become_followhost']) }}" class="Footer__link">Become a Followhost</a>
                        @endunless
                        <a href="{{ route('university') }}" class="Footer__link"><span class="hidden-sm">Followout</span> University</a>
                        <div class="Footer__link" data-toggle="modal" data-target="#contact-support-modal">Contact us</div>
                    </div>
                @endunless
                <div class="Footer__copyright">© {{ date('Y') }} FollowOut LLC</div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    @if (Str::endsWith(Route::currentRouteAction(), 'UsersController@askForAccountActivation'))
        @include('includes.modals.contact-support', ['subject' => 'Activation email not received'])
    @else
        @include('includes.modals.contact-support')
    @endif
@endpush
