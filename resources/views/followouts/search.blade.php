@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                {{-- Ads --}}
            </div>

            <div class="col-md-8">
                <div class="Heading Heading--section UsersGrid__heading">{{ $data['followouts']->count() }} {{ Str::plural('followout', $data['followouts']->count()) }} found</div>
                <div class="UsersGrid">
                    @forelse ($data['followouts']->chunk(3) as $chunk)
                        @foreach ($chunk as $followout)
                            <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}" class="col-sm-6 col-md-4 UsersGrid__item">
                                <div class="UsersGrid__item-image-wrap">
                                    <img class="UsersGrid__item-image" src="{{ $followout->flyerURL() }}"></img>
                                </div>
                                <div class="UsersGrid__item-name">
                                    {{ $followout->title }}
                                </div>
                            </a>
                        @endforeach
                    @empty
                        <div class="text-muted">Nothing found.</div>
                    @endforelse
                </div>
            </div>

            <div class="col-md-2">
                {{-- Sponsored Followouts --}}
            </div>
        </div>
    </div>
</div>
@endsection
