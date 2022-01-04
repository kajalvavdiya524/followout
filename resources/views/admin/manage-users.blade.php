@extends('layouts.app')

@section('content')
@if ($users->count() > 0)
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">{{ $users->count() }} {{ Str::plural('friend', $users->count()) }} found</div>
                        </div>

                        <div class="Block__body">
                            @foreach ($users as $user)
                                <h4 style="margin-top: 0;">
                                    {{ $user->name }}
                                </h4>
                                <ul class="list-group">
                                    <li class="list-group-item">Phone: {{ $user->phone_number }}</li>
                                    <li class="list-group-item">Email: {{ $user->email }}</li>
                                    <li class="list-group-item">From: {{ $user->fullAddress() }}</li>
                                    <li class="list-group-item">Joined {{ $user->created_at->diffForHumans() }}</li>
                                    <li class="list-group-item">Last seen {{ $user->last_seen->diffForHumans() }}</li>
                                </ul>
                                <a class="Button Button--danger" href="{{ route('users.show', [ 'user' => $user->id ]) }}">
                                    View
                                </a>
                                @unless ($loop->last)
                                    <hr style="margin-left: -15px; margin-right: -15px;">
                                @endunless
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($usersToBeDeleted->count() > 0)
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">{{ $usersToBeDeleted->count() }} {{ Str::plural('user', $usersToBeDeleted->count()) }} pending account deactivation</div>
                        </div>

                        <div class="Block__body">
                            @foreach ($usersToBeDeleted as $user)
                                <h4 style="margin-top: 0;">
                                    {{ $user->name }}
                                </h4>
                                <ul class="list-group">
                                    <li class="list-group-item">Phone: {{ $user->phone_number }}</li>
                                    <li class="list-group-item">Email: {{ $user->email }}</li>
                                    <li class="list-group-item">From: {{ $user->fullAddress() }}</li>
                                    <li class="list-group-item">Joined {{ $user->created_at->diffForHumans() }}</li>
                                    <li class="list-group-item">Last seen {{ $user->last_seen->diffForHumans() }}</li>
                                    <li class="list-group-item">Requested deletion {{ $user->requested_account_deletion_at->diffForHumans() }}</li>
                                </ul>
                                <a class="Button Button--danger" href="{{ route('users.show', [ 'user' => $user->id ]) }}">
                                    View
                                </a>
                                @unless ($loop->last)
                                    <hr style="margin-left: -15px; margin-right: -15px;">
                                @endunless
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($users->count() === 0 && $usersToBeDeleted->count() === 0)
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="text-muted text-center">
                        There are no users that need to be managed.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
