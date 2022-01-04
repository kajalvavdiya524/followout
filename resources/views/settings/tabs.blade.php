@php
    $currentTab = Route::currentRouteName();
@endphp

<div class="Tabs Tabs--settings">
    <a href="{{ route('settings.account') }}" class="Tab {{ $currentTab === 'settings.account' ? 'Tab--active' : '' }}">
        <i class="Tab__icon fas fa-fw fa-user"></i>
        <span class="Tab__name">
            Account
        </span>
    </a>
    <a href="{{ route('settings.security') }}" class="Tab {{ $currentTab === 'settings.security' ? 'Tab--active' : '' }}">
        <i class="Tab__icon fas fa-fw fa-lock"></i>
        <span class="Tab__name">
            Security
        </span>
    </a>
    <a href="{{ route('settings.payments') }}" class="Tab {{ $currentTab === 'settings.payments' ? 'Tab--active' : '' }}">
        <i class="Tab__icon far fa-fw fa-credit-card"></i>
        <span class="Tab__name">
            Payments
        </span>
    </a>
    <a href="{{ route('settings.notifications') }}" class="Tab {{ $currentTab === 'settings.notifications' ? 'Tab--active' : '' }}">
        <i class="Tab__icon fas fa-fw fa-bell"></i>
        <span class="Tab__name">
            Notifications
        </span>
    </a>
</div>
