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

                                <div class="form-group {{ $errors->has('flyer') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Flyer</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview {{ $followout->hasVideoFlyer() && !$followout->flyer->video->isProcessed() ? 'ImageInputWithPreview--video-processing' : '' }}">
                                            @if ($followout->hasFlyer())
                                                @if ($followout->hasVideoFlyer())
                                                    <div
                                                        class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer {{ $followout->isGeoCoupon() ? 'ImageInputWithPreview__picture--geo–coupon' : '' }}"
                                                        data-for="flyer"
                                                        style="display: none;"
                                                    ></div>
                                                    <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video {{ !$followout->flyer->video->isProcessed() ? 'ImageInputWithPreview__picture--video-processing' : '' }}" src="{{ $followout->videoFlyerURL() }}" data-for="flyer" img-loaded="true" followout-flyer-id="{{ $followout->flyer->id }}" autoplay muted>
                                                        <source>
                                                    </video>
                                                @else
                                                    <div
                                                        class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer {{ $followout->isGeoCoupon() ? 'ImageInputWithPreview__picture--geo–coupon' : '' }}"
                                                        data-for="flyer"
                                                        img-loaded="true"
                                                        style="background-image: url('{{ $followout->flyerURL() }}');"
                                                        followout-flyer-id="{{ $followout->flyer->id }}"
                                                    ></div>
                                                    <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video" data-for="flyer" style="display: none;" autoplay muted>
                                                        <source>
                                                    </video>
                                                @endif
                                            @else
                                                <div class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer {{ $followout->isGeoCoupon() ? 'ImageInputWithPreview__picture--geo–coupon' : '' }}" data-for="flyer"></div>
                                                <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video" data-for="flyer" style="display: none;" autoplay muted>
                                                    <source>
                                                </video>
                                            @endif
                                            <input id="flyer" class="ImageInputWithPreview__input" type="file" name="flyer" accept="image/gif,image/x-png,image/jpeg,video/mp4,video/quicktime">
                                        </div>

                                        <div class="ImageInputWithPreview__help-text">Images: JPG/PNG/GIF, 150x225px minimum</div>
                                        <div class="ImageInputWithPreview__help-text">Videos: MP4/M4V/MOV {{ auth()->user()->isAdmin() ? 'up to 100MB' : 'up to 5 seconds, up to 100MB' }}</div>

                                        <select name="removed_flyer[]" multiple hidden>
                                            @if ($followout->hasFlyer())
                                                <option value="{{ $followout->flyer->id }}">{{ $followout->flyer->id }}</option>
                                            @endif
                                        </select>

                                        @if ($errors->has('flyer'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('flyer') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('picture1') || $errors->has('picture2') || $errors->has('picture3') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Additional Pictures</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview">
                                            <div
                                                data-for="picture1"
                                                class="ImageInputWithPreview__picture ImageInputWithPreview__picture--picture"
                                                @if ($followout->hasPicture(0))
                                                    img-loaded="true"
                                                    style="background-image: url('{{ $followout->pictureURL(0) }}');"
                                                    followout-picture-id="{{ $followout->picture(0)->id }}"
                                                @endif
                                            >
                                            </div>
                                            <div
                                                data-for="picture2"
                                                class="ImageInputWithPreview__picture ImageInputWithPreview__picture--picture"
                                                @if ($followout->hasPicture(1))
                                                    img-loaded="true"
                                                    style="background-image: url('{{ $followout->pictureURL(1) }}');"
                                                    followout-picture-id="{{ $followout->picture(1)->id }}"
                                                @endif
                                            >
                                            </div>
                                            <div
                                                data-for="picture3"
                                                class="ImageInputWithPreview__picture ImageInputWithPreview__picture--picture"
                                                @if ($followout->hasPicture(2))
                                                    img-loaded="true"
                                                    style="background-image: url('{{ $followout->pictureURL(2) }}');"
                                                    followout-picture-id="{{ $followout->picture(2)->id }}"
                                                @endif
                                            >
                                            </div>

                                            <input id="picture1" class="ImageInputWithPreview__input" type="file" name="picture1" accept="image/x-png,image/jpeg">
                                            <input id="picture2" class="ImageInputWithPreview__input" type="file" name="picture2" accept="image/x-png,image/jpeg">
                                            <input id="picture3" class="ImageInputWithPreview__input" type="file" name="picture3" accept="image/x-png,image/jpeg">

                                            <select name="removed_pictures[]" multiple hidden>
                                                @foreach ($followout->pictures as $avatar)
                                                    <option value="{{ $avatar->id }}">{{ $avatar->id }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="ImageInputWithPreview__help-text">150x150px minimum</div>

                                        @if ($errors->has('picture1'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('picture1') }}</strong>
                                            </span>
                                        @endif

                                        @if ($errors->has('picture2'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('picture2') }}</strong>
                                            </span>
                                        @endif

                                        @if ($errors->has('picture3'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('picture3') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title" class="col-md-4 control-label is-required">Followout Title</label>

                                    <div class="col-md-6">
                                        <input id="title" type="text" class="form-control" name="title" value="{{ $followout->title }}" placeholder="St. Patrick's Day Parade" readonly>
                                    </div>
                                </div>

                                @if ($followout->isGeoCoupon())
                                    <input type="hidden" name="description" value="{{ $followout->description }}">
                                @else
                                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                        <label for="description" class="col-md-4 control-label is-required">Followout Description</label>

                                        <div class="col-md-6">
                                            <textarea id="description" name="description" rows="3" class="form-control" required>{{ old('description') ?: $followout->description }}</textarea>

                                            @if ($errors->has('description'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('description') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

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

                                <div class="form-group {{ $errors->has('experience_categories') ? 'has-error' : '' }}">
                                    <label for="experience_categories" class="col-md-4 control-label is-required">Followout Experience</label>

                                    <div class="col-md-6">
                                        <select id="experience_categories" class="selectize" name="experience_categories[]" multiple required>
                                            <option value="">Select experience categories</option>
                                            @foreach ($data['followout_categories'] as $category)
                                                @if (count((array) old('experience_categories')) > 0)
                                                    <option value="{{ $category->id }}" {{ in_array($category->id, (array) old('experience_categories')) ? 'selected' : null }}>{{ $category->name }}</option>
                                                @else
                                                    <option value="{{ $category->id }}" {{ in_array($category->id, $followout->experience_categories->pluck('_id')->all()) ? 'selected' : null }}>{{ $category->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>

                                        @if ($errors->has('experience_categories'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('experience_categories') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('tickets_url') ? 'has-error' : '' }}">
                                    <label for="tickets_url" class="col-md-4 control-label">Tickets URL</label>

                                    <div class="col-md-6">
                                        <input id="tickets_url" type="text" class="form-control" name="tickets_url" value="{{ old('tickets_url') ?: $followout->tickets_url }}" placeholder="http://example.com">

                                        @if ($errors->has('tickets_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('tickets_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('external_info_url') ? 'has-error' : '' }}">
                                    <label for="external_info_url" class="col-md-4 control-label">External Info URL</label>

                                    <div class="col-md-6">
                                        <input id="external_info_url" type="text" class="form-control" name="external_info_url" value="{{ old('external_info_url') ?: $followout->external_info_url }}" placeholder="http://example.com">

                                        @if ($errors->has('external_info_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('external_info_url') }}</strong>
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
