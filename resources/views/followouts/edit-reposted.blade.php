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
