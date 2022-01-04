@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            {{-- Ads --}}
            <div class="col-md-2"></div>

            <div class="col-md-8">
                <div class="UsersGrid">
                    @if (is_null($data['category']))
                        {{-- Experience categories --}}
                        <div class="Heading Heading--section UsersGrid__heading">Followees by experience</div>
                    @else
                        {{-- Experience category users --}}
                        <div class="Heading Heading--section UsersGrid__heading">Followees: {{ $data['category']->name }}</div>
                    @endif

                    @if (is_null($data['category']))
                        @forelse ($data['followout_categories']->chunk(3) as $chunk)
                            @foreach ($chunk as $category)
                                <a href="{{ route('users.index.followees', ['category' => $category->id]) }}" class="col-sm-6 col-md-4 UsersGrid__item">
                                    @php
                                        $randomUsers = \App\User::withAvatar()->followees()->whereIn('followout_category_ids', [$category->id])->orderBy('id', 'desc')->get();
                                        $randomUser = $randomUsers->isEmpty() ? null : $randomUsers->random();
                                    @endphp
                                    @unless (is_null($randomUser))
                                        <img class="UsersGrid__item-image" src="{{ $randomUser->avatarURL() }}"></img>
                                    @else
                                        <img class="UsersGrid__item-image" src="{{ url('/img/user-pic-default.png') }}"></img>
                                    @endunless
                                    <div class="UsersGrid__item-name">
                                        {{ $category->name }}
                                    </div>
                                </a>
                            @endforeach
                        @empty
                            <div class="text-muted">Nothing here yet.</div>
                        @endforelse
                    @else
                        @forelse ($data['users']->chunk(3) as $chunk)
                            @foreach ($chunk as $user)
                                <a href="{{ route('users.show', ['user' => $user->id]) }}" class="col-sm-6 col-md-4 UsersGrid__item">
                                    @if ($user->hasAvatar())
                                        <img class="UsersGrid__item-image" src="{{ $user->avatarURL() }}"></img>
                                    @else
                                        <img class="UsersGrid__item-image" src="{{ url('/img/user-pic-default.png') }}"></img>
                                    @endif
                                    <div class="UsersGrid__item-name">
                                        {{ $user->name }}
                                    </div>
                                </a>
                            @endforeach
                        @empty
                            <div class="text-muted">No one's here yet.</div>
                        @endforelse
                    @endif
                </div>
            </div>

            {{-- Sponsored Followouts --}}
            <div class="col-md-2"></div>
        </div>
    </div>
</div>
@endsection
