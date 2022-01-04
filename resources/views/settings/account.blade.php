@extends('layouts.app')

@section('content')
    <div class="Section">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    @include('settings.tabs')

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">Account type</div>
                        </div>
                        <div class="Block__body">
                            Your account type is <strong>{{ auth()->user()->role }}</strong>.
                            @if (auth()->user()->role_expires_at)
                                <br>
                                <br>
                                Your account will be converted back to <strong>friend</strong> type in {{ auth()->user()->role_expires_at->diffForHumans() }}.
                            @endif
                        </div>
                    </div>

                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">Followouts</div>
                        </div>
                        <div class="Block__body">
                            <form id="followout-settings-form" class="Form form-horizontal" role="form" method="POST" action="{{ route('settings.followouts.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group {{ $errors->has('flyer') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Default flyer</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview {{ auth()->user()->hasDefaultVideoFlyer() && !auth()->user()->default_flyer->video->isProcessed() ? 'ImageInputWithPreview--video-processing' : '' }}">
                                            @if (auth()->user()->hasDefaultFlyer())
                                                @if (auth()->user()->hasDefaultVideoFlyer())
                                                    <div
                                                        class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer"
                                                        data-for="flyer"
                                                        style="display: none;"
                                                    ></div>
                                                    <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video {{ !auth()->user()->default_flyer->video->isProcessed() ? 'ImageInputWithPreview__picture--video-processing' : '' }}" src="{{ auth()->user()->defaultFlyerURL(true) }}" data-for="flyer" img-loaded="true" followout-flyer-id="{{ auth()->user()->default_flyer->id }}" autoplay loop muted>
                                                        <source>
                                                    </video>
                                                @else
                                                    <div
                                                        class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer"
                                                        data-for="flyer"
                                                        img-loaded="true"
                                                        style="background-image: url('{{ auth()->user()->defaultFlyerURL() }}');"
                                                        followout-flyer-id="{{ auth()->user()->default_flyer->id }}"
                                                    ></div>
                                                    <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video" data-for="flyer" style="display: none;" autoplay muted>
                                                        <source>
                                                    </video>
                                                @endif
                                            @else
                                                <div class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer" data-for="flyer"></div>
                                                <video class="ImageInputWithPreview__picture ImageInputWithPreview__picture--flyer ImageInputWithPreview__picture--video" data-for="flyer" style="display: none;" autoplay muted>
                                                    <source>
                                                </video>
                                            @endif

                                            <input id="flyer" class="ImageInputWithPreview__input" type="file" name="flyer" accept="image/gif,image/x-png,image/jpeg,video/mp4,video/quicktime">
                                        </div>

                                        <div class="ImageInputWithPreview__help-text">Images: JPG/PNG/GIF, 150x225px minimum</div>
                                        <div class="ImageInputWithPreview__help-text">Videos: MP4/M4V/MOV, {{ auth()->user()->isAdmin() ? 'up to 100MB' : 'up to 5 seconds, up to 100MB' }}</div>

                                        <select name="removed_flyer[]" multiple hidden>
                                            @if (auth()->user()->hasDefaultFlyer())
                                                <option value="{{ auth()->user()->default_flyer->id }}">{{ auth()->user()->default_flyer->id }}</option>
                                            @endif
                                        </select>

                                        @if ($errors->has('flyer'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('flyer') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if (auth()->user()->isFollowhost())
                                    @php
                                        $canHaveDefaultFollowout = auth()->user()->subscribed() && auth()->user()->hasDefaultFollowout();
                                        $defaultFollowout = auth()->user()->followouts()->where('is_default', true)->first();
                                    @endphp

                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Default Followout</label>

                                        <div class="col-md-6">
                                            <div class="Checkbox {{ !$canHaveDefaultFollowout ? 'Checkbox--disabled' : '' }}">
                                                <input id="show_default_followout" type="checkbox" name="show_default_followout" class="Checkbox__input" {{ (($errors->any() && old('show_default_followout')) || ($defaultFollowout && !$defaultFollowout->isHidden())) ? 'checked' : '' }}>
                                                <label for="show_default_followout" class="Checkbox__label">Display default Followout</label>
                                            </div>
                                            <div class="Checkbox {{ !$canHaveDefaultFollowout ? 'Checkbox--disabled' : '' }}">
                                                <input id="auto_show_default_followouts" type="checkbox" name="auto_show_default_followouts" class="Checkbox__input" {{ (($errors->any() && old('auto_show_default_followouts')) || auth()->user()->auto_show_default_followouts !== false) ? 'checked' : '' }}>
                                                <label for="auto_show_default_followouts" class="Checkbox__label">Automatically display default Followout if there are no upcoming or ongoing public followouts</label>
                                            </div>
                                            @unless ($canHaveDefaultFollowout)
                                                <div class="text-muted">
                                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#subscription-required-modal">Subscribe</a> to enable default Followouts.
                                                </div>
                                            @endunless
                                        </div>
                                    </div>
                                @endif

                                @unless (auth()->user()->isFollowhost())
                                    <div class="form-group">
                                        <label class="col-md-4 control-label">Autosubcribe to Followhosts</label>
                                        <div class="col-md-6">
                                            <div class="Checkbox">
                                                <input id="autosubcribe_to_followhosts" type="checkbox" name="autosubcribe_to_followhosts" class="Checkbox__input" {{ (($errors->any() && old('autosubcribe_to_followhosts')) || auth()->user()->autosubcribe_to_followhosts) ? 'checked' : '' }}>
                                                <label for="autosubcribe_to_followhosts" class="Checkbox__label">Automatically subscribe to businesses to receive deals or offers</label>
                                            </div>
                                        </div>
                                    </div>
                                @endunless

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Help with promotion</label>
                                    <div class="col-md-6">
                                        <div class="Checkbox">
                                            <input id="available_for_promotion" type="checkbox" name="available_for_promotion" class="Checkbox__input" {{ (($errors->any() && old('available_for_promotion')) || auth()->user()->available_for_promotion) ? 'checked' : '' }}>
                                            <label for="available_for_promotion" class="Checkbox__label">I can help promote businesses</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="Block__footer">
                            <a class="Button Button--danger" onclick="event.preventDefault(); document.getElementById('followout-settings-form').submit();">Save changes</a>
                        </div>
                    </div>

                    @if (SocialHelper::facebookConnected(auth()->user()))
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">Social Networks</div>
                            </div>
                            <div class="Block__body">
                                <form class="Form form-horizontal" role="form" method="POST" action="#">
                                    <div class="form-group form-group--last">
                                        <label class="col-md-4 control-label">Facebook</label>

                                        <div class="col-md-6">
                                            @if (SocialHelper::facebookConnected(auth()->user()))
                                                <a class="Button Button--facebook" href="{{ route('settings.social.disconnect', ['provider' => 'facebook']) }}">
                                                    <i class="fab fa-fw fa-facebook-f"></i>
                                                    Disconnect Facebook
                                                </a>
                                            @else
                                                {{--
                                                <a class="Button Button--facebook" href="{{ route('login.facebook') }}">
                                                    <i class="fab fa-fw fa-facebook-f"></i>
                                                    Connect Facebook
                                                </a>
                                                --}}
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @unless (auth()->user()->isAdmin())
                        @unless (auth()->user()->markedForDeletion())
                            <div class="Block">
                                <div class="Block__header">
                                    <div class="Block__heading">Deactivate Account</div>
                                </div>
                                <div class="Block__body">
                                    You can deactivate your account here.
                                </div>
                                <div class="Block__footer">
                                    <a href="{{ route('users.suicide') }}" class="Button Button--danger">Deactivate Account</a>
                                </div>
                            </div>
                        @endunless
                    @endunless
                </div>
            </div>
        </div>
    </div>
@endsection
