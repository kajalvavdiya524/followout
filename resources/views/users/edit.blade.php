@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Enhance profile
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form Form--block-padding form-horizontal" role="form" method="POST" action="{{ action('UsersController@update', ['user' => $user->id]) }}" enctype="multipart/form-data">
                                {{ method_field('PUT') }}
                                @csrf

                                @if (auth()->user()->isFollowhost())
                                    <div class="form-group {{ $errors->has('picture1') ? 'has-error' : '' }}">
                                        <label class="col-md-4 control-label is-required">Company logo</label>

                                        <div class="col-md-6">
                                            <div class="ImageInputWithPreview">
                                                <div
                                                    data-for="picture1"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if ($user->hasAvatar(0))
                                                        style="background-image:url('{{ $user->avatarURL(0) }}')"
                                                        data-picture-id="{{ $user->avatars->get(0)->id }}"
                                                    @endif
                                                    img-loaded="false"
                                                >
                                                </div>
                                                <input id="picture1" class="ImageInputWithPreview__input" type="file" name="picture1" accept="image/x-png,image/jpeg">
                                            </div>

                                            <div class="ImageInputWithPreview__help-text">150x150px minimum</div>

                                            @if ($errors->has('picture1'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('picture1') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->has('picture2') || $errors->has('picture3') ? 'has-error' : '' }}">
                                        <label class="col-md-4 control-label">Additional profile pictures</label>

                                        <div class="col-md-6">
                                            <div class="ImageInputWithPreview">
                                                <div
                                                    data-for="picture2"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if ($user->hasAvatar(1))
                                                        style="background-image:url('{{ $user->avatarURL(1) }}')"
                                                        data-picture-id="{{ $user->avatars->get(1)->id }}"
                                                    @endif
                                                >
                                                </div>
                                                <div
                                                    data-for="picture3"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if($user->hasAvatar(2))
                                                        style="background-image:url('{{ $user->avatarURL(2) }}')"
                                                        data-picture-id="{{ $user->avatars->get(2)->id }}"
                                                    @endif
                                                >
                                                </div>
                                                <input id="picture2" class="ImageInputWithPreview__input" type="file" name="picture2" accept="image/x-png,image/jpeg">
                                                <input id="picture3" class="ImageInputWithPreview__input" type="file" name="picture3" accept="image/x-png,image/jpeg">
                                            </div>

                                            <div class="ImageInputWithPreview__help-text">150x150px minimum</div>

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

                                    <select name="removed_pictures[]" multiple hidden>
                                        @foreach ($user->avatars as $avatar)
                                            <option value="{{ $avatar->id }}">{{ $avatar->id }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div class="form-group {{ $errors->has('picture1') || $errors->has('picture2') || $errors->has('picture3') ? 'has-error' : '' }}">
                                        <label class="col-md-4 control-label is-required">Profile Picture</label>

                                        <div class="col-md-6">
                                            <div class="ImageInputWithPreview">
                                                <div
                                                    data-for="picture1"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if($user->hasAvatar(0))
                                                        style="background-image:url('{{ $user->avatarURL(0) }}')"
                                                        data-picture-id="{{ $user->avatars->get(0)->id }}"
                                                    @endif
                                                    img-loaded="false"
                                                >
                                                </div>
                                                <div
                                                    data-for="picture2"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if ($user->hasAvatar(1))
                                                        style="background-image:url('{{ $user->avatarURL(1) }}')"
                                                        data-picture-id="{{ $user->avatars->get(1)->id }}"
                                                    @endif
                                                >
                                                </div>
                                                <div
                                                    data-for="picture3"
                                                    class="ImageInputWithPreview__picture ImageInputWithPreview__picture--avatar"
                                                    @if($user->hasAvatar(2))
                                                        style="background-image:url('{{ $user->avatarURL(2) }}')"
                                                        data-picture-id="{{ $user->avatars->get(2)->id }}"
                                                    @endif
                                                >
                                                </div>
                                                <input id="picture1" class="ImageInputWithPreview__input" type="file" name="picture1" accept="image/x-png,image/jpeg">
                                                <input id="picture2" class="ImageInputWithPreview__input" type="file" name="picture2" accept="image/x-png,image/jpeg">
                                                <input id="picture3" class="ImageInputWithPreview__input" type="file" name="picture3" accept="image/x-png,image/jpeg">

                                                <select name="removed_pictures[]" multiple hidden>
                                                    @foreach ($user->avatars as $avatar)
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
                                @endif

                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="col-md-4 control-label is-required">{{ $user->isFollowhost() ? 'Company Name' : 'Name' }}</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') ? old('name') : $user->name }}" placeholder="John Doe" required>

                                        @if ($errors->has('name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                                    <label for="email" class="col-md-4 control-label is-required">E-Mail Address</label>

                                    <div class="col-md-6">
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') ? old('email') : $user->email }}" placeholder="johndoe@example.com" required>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('phone_number') ? 'has-error' : '' }}">
                                    <label for="phone_number" class="col-md-4 control-label">Mobile Phone</label>

                                    <div class="col-md-6">
                                        <input id="phone_number" type="text" class="form-control" name="phone_number_dummy" value="{{ old('phone_number') ? old('phone_number') : $user->phone_number }}" placeholder="555 111 22 33">

                                        @if ($errors->has('phone_number'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('phone_number') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Profile Privacy</label>
                                    <div class="col-md-6">
                                        <select id="privacy_type" class="selectize" name="privacy_type" required>
                                            @if (Gate::denies('set-profile-privacy-type-public'))
                                                <option value="public" disabled>Public (profile visible and searchable by everyone)</option>
                                                <option value="private" selected>Private (profile hidden from everyone)</option>
                                            @else
                                                @if (old('privacy_type'))
                                                    <option value="public" {{ old('privacy_type') == 'public' ? 'selected' : null }}>Public (profile visible and searchable by everyone)</option>
                                                    <option value="private" {{ old('privacy_type') == 'private' ? 'selected' : null }}>Private (profile hidden from everyone)</option>
                                                @else
                                                    <option value="public" {{ $user->isPublic() ? 'selected' : null }}>Public (profile visible and searchable by everyone)</option>
                                                    <option value="private" {{ $user->isPrivate() ? 'selected' : null }}>Private (profile hidden from everyone)</option>
                                                @endif
                                            @endif
                                        </select>

                                        @if (Gate::denies('set-profile-privacy-type-public'))
                                            <div class="text-center text-muted" style="margin-top: 10px;">
                                                You cannot list profile as public.
                                            </div>
                                        @endif

                                        @if ($errors->has('privacy_type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('privacy_type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @unless ($user->isFollowhost())
                                    <div class="form-group {{ $errors->has('gender') ? 'has-error' : '' }}">
                                        <label for="gender" class="col-md-4 control-label">Gender</label>

                                        <div class="col-md-6">
                                            <select id="gender" class="selectize" name="gender">
                                                <option value="">Select gender</option>
                                                @if (old('gender'))
                                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : null }}>Male</option>
                                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : null }}>Female</option>
                                                @else
                                                    <option value="male" {{ $user->isMale() ? 'selected' : null }}>Male</option>
                                                    <option value="female" {{ $user->isFemale() ? 'selected' : null }}>Female</option>
                                                @endif
                                            </select>

                                            @if ($errors->has('gender'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('gender') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group {{ $errors->has('birthday') ? 'has-error' : '' }}">
                                        <label for="birthday" class="col-md-4 control-label">Birthday</label>

                                        <div class="col-md-6">
                                            <input id="birthday" type="text" class="form-control" name="birthday" value="{{ old('birthday') ? old('birthday') : ($user->birthday ? $user->birthday->format(config('followouts.date_format')) : null) }}" placeholder="MM/DD/YYYY">

                                            @if ($errors->has('birthday'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('birthday') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endunless

                                <div class="form-group {{ $errors->has('account_categories') ? 'has-error' : '' }}">
                                    <label for="account_categories" class="col-md-4 control-label is-required">Experience</label>

                                    <div class="col-md-6">
                                        <select id="account_categories" class="selectize" name="account_categories[]" multiple required>
                                            @if (count((array) old('account_categories')))
                                                @foreach ($data['followout_categories'] as $category)
                                                    <option value="{{ $category->id }}" {{ in_array($category->id, (array) old('account_categories')) ? 'selected' : null }}>{{ $category->name }}</option>
                                                @endforeach
                                            @else
                                                @foreach ($data['followout_categories'] as $category)
                                                    <option value="{{ $category->id }}" {{ in_array($category->id, $user->account_categories->pluck('_id')->toArray()) ? 'selected' : null }}>{{ $category->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>

                                        @if ($errors->has('account_categories'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('account_categories') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('lat') || $errors->has('lng') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">
                                        @if (auth()->user()->isFollowhost())
                                            Default Followout Location
                                        @else
                                            Mailing Address
                                        @endif
                                    </label>

                                    <div class="col-md-6">
                                        <input id="location" type="text" class="form-control" name="location" placeholder="Start typing..." onkeypress="return event.keyCode != 13;" value="{{ old('location') }}">
                                        <br>
                                        <div id="map"></div>

                                        @if (Request::secure())
                                            <div class="text-center" style="margin-top: 10px;">
                                                <a href="javascript:void(0);" onclick="getLocation()" class="Button Button--xs Button--danger">Get current location</a>
                                            </div>
                                        @else
                                            <div class="text-center text-muted" style="margin-top: 10px;">
                                                Set your location by clicking on the map.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('lat') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label is-required">Latitude</label>

                                    <div class="col-md-6">
                                        <input id="lat" type="text" name="lat" value="{{ old('lat') ?: ($user->lat ?: '0') }}" class="form-control" readonly>

                                        @if ($errors->has('lat'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('lat') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('lng') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label is-required">Longitude</label>

                                    <div class="col-md-6">
                                        <input id="lng" type="text" name="lng" value="{{ old('lng') ?: ($user->lng ?: '0') }}" class="form-control" readonly>

                                        @if ($errors->has('lng'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('lng') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }}">
                                    <label for="address" class="col-md-4 control-label {{ $user->isFollowhost() ? 'is-required' : '' }}">Mailing Address</label>

                                    <div class="col-md-6">
                                        <input id="address" type="text" class="form-control" name="address" value="{{ old('address') ? old('address') : $user->address }}" placeholder="439 Karley Loaf Suite 897">

                                        @if ($errors->has('address'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('address') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }}">
                                    <label for="city" class="col-md-4 control-label {{ $user->isFollowhost() ? 'is-required' : '' }}">City</label>

                                    <div class="col-md-6">
                                        <input id="city" type="text" class="form-control" name="city" value="{{ old('city') ? old('city') : $user->city }}" placeholder="San Francisco">

                                        @if ($errors->has('city'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('city') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }}">
                                    <label for="state" class="col-md-4 control-label">State</label>

                                    <div class="col-md-6">
                                        <input id="state" type="text" class="form-control" name="state" value="{{ old('state') ? old('state') : $user->state }}" placeholder="California">

                                        @if ($errors->has('state'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('state') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('zip_code') ? 'has-error' : '' }}">
                                    <label for="zip_code" class="col-md-4 control-label is-required">Zip</label>

                                    <div class="col-md-6">
                                        <input id="zip_code" type="text" class="form-control" name="zip_code" value="{{ old('zip_code') ? old('zip_code') : $user->zip_code }}" placeholder="12345" required>

                                        @if ($errors->has('zip_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('zip_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }}">
                                    <label for="country_id" class="col-md-4 control-label {{ $user->isFollowhost() ? 'is-required' : '' }}">Country</label>

                                    <div class="col-md-6">
                                        <select id="country_id" class="selectize" name="country_id">
                                            <option value="">Select country...</option>
                                            @foreach ($data['countries'] as $country)
                                                @if (old('country_id'))
                                                    <option value="{{ $country->id }}" data-data="{{ $country->toJson() }}" {{ old('country_id') == $country->id ? 'selected' : null }}>{{ $country->name }}</option>
                                                @else
                                                    <option value="{{ $country->id }}" data-data="{{ $country->toJson() }}" {{ $user->country_id == $country->id ? 'selected' : null }}>{{ $country->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>

                                        @if ($errors->has('country_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('country_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('website') ? 'has-error' : '' }}">
                                    <label for="website" class="col-md-4 control-label">Website</label>

                                    <div class="col-md-6">
                                        <input id="website" type="text" class="form-control" name="website" value="{{ old('website') ? old('website') : $user->website }}" placeholder="http://example.com">

                                        @if ($errors->has('website'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('website') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('video_url') ? 'has-error' : '' }}">
                                    <label for="video_url" class="col-md-4 control-label">Show URL</label>

                                    <div class="col-md-6">
                                        <input id="video_url" type="text" class="form-control" name="video_url" value="{{ old('video_url') ? old('video_url') : $user->video_url }}" placeholder="http://example.com">

                                        @if ($errors->has('video_url'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('video_url') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @unless ($user->isFollowhost())
                                    <div class="form-group {{ $errors->has('education') ? 'has-error' : '' }}">
                                        <label for="education" class="col-md-4 control-label">Education</label>

                                        <div class="col-md-6">
                                            <input id="education" type="text" class="form-control" name="education" value="{{ old('education') ? old('education') : $user->education }}">

                                            @if ($errors->has('education'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('education') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endunless

                                <div class="form-group {{ $errors->has('keywords') ? 'has-error' : '' }}">
                                    <label for="keywords" class="col-md-4 control-label">Keywords</label>

                                    <div class="col-md-6">
                                        <input id="keywords" type="text" class="form-control" name="keywords" value="{{ old('keywords') ? old('keywords') : $user->keywords }}" placeholder="pizza, party, los-angeles">

                                        @if ($errors->has('keywords'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('keywords') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if ($user->isFollowhost())
                                    <div class="form-group {{ $errors->has('google_business_type') ? 'has-error' : '' }}">
                                        <label for="google_business_type" class="col-md-4 control-label">Business type</label>

                                        <div class="col-md-6">
                                            <select id="google_business_type" class="selectize" name="google_business_type">
                                                <option value="">Select business type...</option>
                                                @foreach (GooglePlacesHelper::getFilterablePlaceTypesForSelect() as $type => $typeFormatted)
                                                    @if (old('google_business_type'))
                                                        <option value="{{ $type }}" {{ old('google_business_type') == $type ? 'selected' : null }}>{{ $typeFormatted }}</option>
                                                    @else
                                                        <option value="{{ $type }}" {{ $user->google_business_type == $type ? 'selected' : null }}>{{ $typeFormatted }}</option>
                                                    @endif
                                                @endforeach
                                            </select>

                                            @if ($errors->has('google_business_type'))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first('google_business_type') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group {{ $errors->has('about') ? 'has-error' : '' }}">
                                    <label for="about" class="col-md-4 control-label">About</label>

                                    <div class="col-md-6">
                                        <textarea id="about" name="about" rows="3" class="form-control">{{ old('about') ? old('about') : $user->about }}</textarea>

                                        @if ($errors->has('about'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('about') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save changes
                                        </button>
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

@if (session()->has('SHOW_PROFILE_PICTURE_TUTORIAL'))
    @push('modals')
        @include('includes.modals.profile-picture-tutorial')
        <script> $(function() { $('#profile-picture-tutorial-modal').modal('show'); }); </script>
    @endpush
@endif

@push('scripts-footer')
    <script>
         $("#phone_number").intlTelInput({
             hiddenInput: 'phone_number',
         });
    </script>

    @include('includes.google-maps-editable')

    <script> $(function() { initMap() }); </script>
@endpush
