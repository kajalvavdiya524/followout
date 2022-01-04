@extends('layouts.app')

@section('content')
<div class="Section">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-md-5 col-md-push-7">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">Filters</div>
                    </div>
                    <div class="Block__body">
                        <form id="filters-form" class="Form form-horizontal" role="form" method="GET" action="{{ route('search.users') }}">
                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label">Name</label>

                                <div class="col-md-8">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ $query['name'] }}" placeholder="Any name">

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('keyword') ? 'has-error' : '' }}">
                                <label for="keyword" class="col-md-4 control-label">Keyword</label>

                                <div class="col-md-8">
                                    <input id="keyword" type="text" class="form-control" name="keyword" value="{{ $query['keyword'] }}" placeholder="Any keyword">

                                    @if ($errors->has('keyword'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('keyword') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('user_type') ? 'has-error' : '' }}">
                                <label for="user_type" class="col-md-4 control-label">User type</label>

                                <div class="col-md-8">
                                    <select id="user_type" class="selectize" name="user_type">
                                        <option value="">Any users</option>
                                        @foreach ($data['userTypes'] as $type => $name)
                                            <option value="{{ $type }}" {{ $query['user_type'] == $type ? 'selected' : null }}>{{ $name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('user_type'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('user_type') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('experience') ? 'has-error' : '' }}">
                                <label for="experience" class="col-md-4 control-label">Experience</label>

                                <div class="col-md-8">
                                    <select id="experience" class="selectize" name="experience">
                                        <option value="">Any experience</option>
                                        @foreach ($data['experience_categories'] as $category)
                                            <option value="{{ $category->id }}" {{ $query['experience'] == $category->id ? 'selected' : null }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('experience'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('experience') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('country') ? 'has-error' : '' }}">
                                <label for="country" class="col-md-4 control-label">Country</label>

                                <div class="col-md-8">
                                    <select id="country" class="selectize" name="country">
                                        <option value="">Any country</option>
                                        @foreach ($data['countries'] as $country)
                                            <option value="{{ $country->id }}" {{ old('country') && old('country') == $query['country'] ? 'selected' : null }}>{{ $country->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('country'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('country') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="Block__footer">
                        <div class="Button Button--danger" onclick="event.preventDefault(); document.getElementById('filters-form').submit();">
                            Search Community
                        </div>
                        @unless (empty(array_filter($query)))
                            <a href="{{ route('search.users') }}" type="submit" class="Button Button--default pull-right">
                                Clear
                            </a>
                        @endunless
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-7 col-md-pull-5">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">Followout Community</div>
                    </div>
                    <div class="Block__body">
                        <div class="SearchResults">
                            @forelse ($data['users'] as $user)
                                <div class="SearchResult">
                                    <div class="SearchResult__picture-container">
                                        <a href="{{ $user->url() }}" class="SearchResult__picture-link">
                                            <img class="SearchResult__picture SearchResult__picture--user" src="{{ $user->avatarURL() }}">
                                        </a>
                                        @if (auth()->check() && auth()->user()->id !== $user->id)
                                            <div class="SearchResult__picture-buttons">
                                                @if (auth()->user()->following($user->id))
                                                    <a href="{{ route('users.unsubscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton FollowButton--following"></a>
                                                @else
                                                    <a href="{{ route('users.subscribe', ['user' => $user->id]) }}" class="Button Button--block FollowButton"></a>
                                                @endif
                                                @if ($user->following(auth()->user()->id))
                                                    @php
                                                        $inviteAttendeeeModalId = 'invite-attendee-modal-'.$user->id;
                                                    @endphp
                                                    <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#{{ $inviteAttendeeeModalId }}">
                                                        Invite to Attend
                                                    </div>
                                                    @push('modals')
                                                        @include('includes.modals.invite-attendee', ['modalId' => $inviteAttendeeeModalId])
                                                    @endpush
                                                @endif
                                                @if (Gate::allows('invite-followee', $user))
                                                    @php
                                                        $inviteFolloweeModalId = 'invite-followee-modal-'.$user->id;
                                                    @endphp
                                                    <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#{{ $inviteFolloweeModalId }}">
                                                        Invite to Present
                                                    </div>
                                                    @push('modals')
                                                        @include('includes.modals.invite-followee', ['modalId' => $inviteFolloweeModalId])
                                                    @endpush
                                                @elseif (auth()->user()->isFollowhost() && !auth()->user()->subscribed())
                                                    <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#subscription-required-modal">
                                                        Invite to Present
                                                    </div>
                                                @endif
                                                @if (Gate::allows('introduce-yourself', $user))
                                                    @php
                                                        $introduceMyselfModalId = 'followee-intro-modal-'.$user->id;
                                                    @endphp
                                                    <div class="Button Button--block Button--danger" data-toggle="modal" data-target="#{{ $introduceMyselfModalId }}">
                                                        Introduce Myself
                                                    </div>
                                                    @push('modals')
                                                        @include('includes.modals.followee-intro', ['modalId' => $introduceMyselfModalId])
                                                    @endpush
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <div class="SearchResult__content-container">
                                        <a href="{{ $user->url() }}" class="SearchResult__title">{{ $user->name }}</a>
                                        <div class="SearchResult__misc">
                                            @if ($user->city || $user->country)
                                                <div class="SearchResult__misc-item">
                                                    <i class="fas fa-fw fa-map-marker-alt"></i>
                                                    {{ $user->city ? $user->city : null }}{{ $user->city && $user->country ? ',' : null }} {{ $user->country ? $user->country->name : null }}
                                                </div>
                                            @endif
                                            @if ($user->website)
                                                <div class="SearchResult__misc-item">
                                                    <i class="fas fa-fw fa-link"></i>
                                                    <a target="_blank" href="{{ $user->website }}">
                                                        {{ $user->website }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        @if ($user->about)
                                            <div class="SearchResult__description">
                                                <strong>About me:</strong>
                                                <br>
                                                {!! nl2br(e($user->about)) !!}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center">
                                    Nothing found.
                                </div>
                            @endforelse
                        </div>
                        @unless ($data['users']->currentPage() === 1 && ($data['users']->currentPage() === $data['users']->lastPage() || $data['users']->lastPage() === 0))
                            <div class="Pagination Pagination--inside-block">
                                {{ $data['users']->appends($query)->links() }}
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
