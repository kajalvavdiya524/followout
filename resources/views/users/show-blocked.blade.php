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
            </div>

            <div class="col-md-5">
                <div class="ProfileInfo">
                    <div class="ProfileInfo__heading">{{ $user->name }}</div>

                    <div class="ProfileInfo__focus">
                        <p class="ProfileInfo__focus-item text-center text-semibold">
                            {{ $user->name }} has restricted access to {{ $user->isFemale() ? 'her' : 'his' }} profile.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Followouts --}}
            <div class="col-md-3">
                &nbsp;
            </div>
        </div>
    </div>
</div>
@endsection
