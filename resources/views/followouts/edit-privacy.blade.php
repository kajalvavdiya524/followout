@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Enhance Followout
                            </div>
                        </div>
                        <div class="Block__body">

                            <form class="Form Form--block-padding form-horizontal" role="form" method="POST" action="{{ route('followouts.update', ['followout' => $followout->id]) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title" class="col-md-4 control-label is-required">Followout Title</label>

                                    <div class="col-md-6">
                                        <input id="title" type="text" class="form-control" name="title" value="{{ $followout->title }}" placeholder="St. Patrick's Day Parade" readonly>
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <label for="description" class="col-md-4 control-label is-required">Followout Description</label>

                                    <div class="col-md-6">
                                        <textarea id="description" name="description" rows="3" class="form-control" readonly>{{ old('description') ?: $followout->description }}</textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="privacy_type" class="col-md-4 control-label">Followout Privacy</label>

                                    <div class="col-md-6">
                                        <select id="privacy_type" class="{{ Gate::allows('set-followout-privacy-type-public') ? 'selectize' : 'selectize-followout-privacy' }}" name="privacy_type" required>
                                            @if (Gate::allows('set-followout-privacy-type-public'))
                                                <option value="public" {{ old('privacy_type') == 'public' ? 'selected' : ($followout->isPublic() ? 'selected' : null) }}>Public</option>
                                            @elseif (auth()->user()->isFollowhost())
                                                <option value="public" disabled>Public (You must be a subscriber)</option>
                                            @endif

                                            <option value="followers" {{ old('privacy_type') == 'followers' ? 'selected' : ($followout->isFollowersOnly() ? 'selected' : null) }}>Visible to Followout Community</option>

                                            @unless ($followout->isGeoCoupon() || $followout->reward_programs()->count() > 0)
                                                <option value="private" {{ old('privacy_type') == 'private' ? 'selected' : ($followout->isPrivate() ? 'selected' : null) }}>Invite only</option>
                                            @endunless
                                        </select>

                                        @if (auth()->user()->isFollowhost() && Gate::denies('set-followout-privacy-type-public'))
                                            @unless (auth()->user()->subscribed())
                                                <div class="text-center text-muted" style="margin-top: 10px;">
                                                    You cannot post public Followouts without a <a href="javascript:void(0);" data-toggle="modal" data-target="#subscription-required-modal">subscription</a>.
                                                </div>
                                            @endunless
                                        @endif

                                        @if ($errors->has('privacy_type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('privacy_type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div class="FollowoutCreateButton">
                                            <div class="FollowoutCreateButton__label">
                                                Click to save changes
                                            </div>
                                            <button type="submit" class="FollowoutCreateButton__button"></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @if (Gate::denies('set-followout-privacy-type-public'))
        @include('includes.modals.followout-cant-be-public')
    @endif
@endpush

@push('scripts-footer')
    @include('followouts.edit-scripts')
@endpush
