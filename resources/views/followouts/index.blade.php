@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                {{-- Ads --}}
            </div>

            <div class="col-md-8">
                @if (is_null($data['category']))
                    {{-- Experience categories --}}
                    <div class="Heading Heading--section UsersGrid__heading">Followouts by experience</div>
                @else
                    {{-- Experience category followouts --}}
                    <div class="Heading Heading--section UsersGrid__heading">{{ $data['category']->name }} followouts</div>
                @endif
                <div class="UsersGrid">
                    @if (is_null($data['category']))
                        @forelse ($data['followout_categories']->chunk(3) as $chunk)
                            @foreach ($chunk as $category)
                                <a href="{{ route('followouts.index', ['category' => $category->id]) }}" class="col-sm-6 col-md-4 UsersGrid__item">
                                    @php
                                        $randomFollowout = FollowoutHelper::getRandomFollowoutWithFlyer(auth()->user(), $category->id);
                                    @endphp
                                    <div class="UsersGrid__item-image-wrap">
                                        @if ($randomFollowout)
                                            <img class="UsersGrid__item-image" src="{{ $randomFollowout->flyerURL() }}"></img>
                                        @else
                                            <img class="UsersGrid__item-image" src="{{ url('/img/flyer-pic-default.png') }}"></img>
                                        @endunless
                                    </div>
                                    <div class="UsersGrid__item-name">
                                        {{ $category->name }}
                                    </div>
                                </a>
                            @endforeach
                        @empty
                            <div class="text-muted">Nothing here yet.</div>
                        @endforelse
                    @else
                        @forelse ($data['followouts']->chunk(3) as $chunk)
                            @foreach ($chunk as $followout)
                                <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}" class="col-sm-6 col-md-4 UsersGrid__item">
                                    <div class="UsersGrid__item-image-wrap">
                                        <img class="UsersGrid__item-image" src="{{ $followout->flyerURL() }}"></img>

                                        <img src="{{ $followout->author->avatarURL() }}" class="UsersGrid__item-avatar" data-toggle="tooltip" title="{{ $followout->author->name }}">
                                    </div>
                                    <div class="UsersGrid__item-name">
                                        {{ $followout->title }}
                                    </div>
                                </a>
                            @endforeach
                        @empty
                            <div class="text-muted">Nothing here yet.</div>
                        @endforelse
                    @endif
                </div>
            </div>

            <div class="col-md-2">
                {{-- Sponsored Followouts --}}
            </div>
        </div>
    </div>
</div>
@endsection
