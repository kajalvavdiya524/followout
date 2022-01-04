@extends('layouts.app')

@section('page-title', $user->name)

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="ProfilePicture">
                    <img class="ProfilePicture__picture" src="{{ $user->defaultAvatarURL() }}" />
                </div>

                @if (auth()->check() && auth()->user()->id !== $user->id)
                    <div class="ProfileButtonsWrap">
                        @if (auth()->user()->following($user->id))
                            <a href="{{ route('users.unsubscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton FollowButton--following">
                                <i class="fas fa-fw fa-check"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="col-md-5">
                <div class="ProfileInfo">
                    <div class="ProfileInfo__heading">{{ $user->name }}</div>

                    <div class="ProfileInfo__focus">
                        <p class="ProfileInfo__focus-item text-center text-semibold">
                            This profile is private.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Followouts --}}
            <div class="col-md-3">
                &nbsp;
            </div>
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
                @endif
            @endforelse
        </div>
    </div>
</div>
@endsection
