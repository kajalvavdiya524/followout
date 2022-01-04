@extends('layouts.app')

@section('page-title', $followout->title . ' statistics')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="StatsFollowoutHeading">
                    <img src="{{ $followout->flyerURL() }}" class="StatsFollowoutHeading__flyer">
                    {{ $followout->title }} statistics
                </div>

                @include('followouts.stats.includes.stats-general', ['followout' => $followout])

                <br>

                <div class="ButtonGroup ButtonGroup--center">
                    <a href="{{ route('followouts.show', ['followout' => $followout->id]) }}" class="Button Button--danger">View Followout</a>
                </div>

                @php
                    if ($followout->isTopParentFollowout()) {
                        $repostedFollowouts = $followout->reposted_followouts;
                    } else {
                        $repostedFollowouts = $followout->child_followouts;
                    }
                @endphp

                @foreach ($repostedFollowouts as $repostedFollowout)
                    @if ($loop->first)
                        <br>
                        <hr>
                        <br>
                    @endif

                    <div class="StatsUserHeading">
                        <img src="{{ $repostedFollowout->author->avatarURL() }}" class="StatsUserHeading__picture">
                        @if ($repostedFollowout->parent_followout->id === $repostedFollowout->getTopParentFollowout()->id || $repostedFollowout->parent_followout->id === $followout->id)
                            <a target="_blank" href="{{ route('users.show', ['user' => $repostedFollowout->author->id]) }}">{{ $repostedFollowout->author->name }}</a> repost statistics
                        @else
                            <a target="_blank" href="{{ route('users.show', ['user' => $repostedFollowout->author->id]) }}">{{ $repostedFollowout->author->name }}</a> repost statistics <span class="StatsUserHeading__misc-info">(invited by <a href="{{ route('users.show', ['user' => $repostedFollowout->parent_followout->author->id]) }}">{{ $repostedFollowout->parent_followout->author->name }}</a>)</span>
                        @endif
                    </div>

                    @include('followouts.stats.includes.stats-general', ['followout' => $repostedFollowout])

                    <br>

                    <div class="ButtonGroup ButtonGroup--center">
                        <a target="_blank" href="{{ route('followouts.show', ['followout' => $repostedFollowout->id]) }}" class="Button Button--danger">View repost</a>
                        <a target="_blank" href="{{ route('followouts.stats', ['followout' => $repostedFollowout->id]) }}" class="Button Button--danger">View repost statistics</a>
                    </div>
                    @unless ($loop->last)
                        <br>
                        <hr>
                        <br>
                    @endunless
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
