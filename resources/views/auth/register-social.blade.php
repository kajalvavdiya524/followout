@extends('layouts.app')

@section('content')
<div class="Section Section--no-padding-mobile">
    <div class="container">
        <div class="row">
            <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                <div class="Block">
                    <div class="Block__header">
                        <div class="Block__heading">
                            Register
                        </div>
                    </div>
                    <div class="Block__body">
                        <form class="Form Form--block-padding form-horizontal" method="POST" action="{{ action('Auth\RegisterController@registerFromSocial') }}" autocomplete="off">
                            {{ csrf_field() }}

                            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                <label for="name" class="col-md-4 control-label is-required">Name</label>

                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name') ?: auth()->user()->name }}" placeholder="John Doe" required>

                                    @if ($errors->has('name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email" class="col-md-4 control-label is-required">E-Mail Address</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ auth()->user()->email }}" placeholder="johndoe@example.com" {{ is_null(auth()->user()->email) ? 'required' : 'readonly' }}>
                                    @unless (is_null(auth()->user()->email))
                                        <div class="text-center text-muted" style="margin-top: 10px;">
                                            You will be able to change your email address later.
                                        </div>
                                    @endunless
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                <label for="password" class="col-md-4 control-label is-required">Password</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required>

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="password-confirm" class="col-md-4 control-label is-required">Confirm Password</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('account_categories') ? 'has-error' : '' }}">
                                <label for="account_categories" class="col-md-4 control-label is-required">Experience</label>

                                <div class="col-md-6">
                                    <select id="account_categories" class="selectize" name="account_categories[]" multiple required>
                                        <option value="">Select experience categories</option>
                                        @foreach ($data['followout_categories'] as $category)
                                            <option value="{{ $category->id }}" {{ in_array($category->id, (array) old('account_categories')) ? 'selected' : null }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('account_categories'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('account_categories') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('is_followhost') ? 'has-error' : '' }}">
                                <label class="col-md-4 control-label">Become a Followhost</label>

                                <div class="col-md-6">
                                    <div class="Checkbox">
                                        <input id="is_followhost" type="checkbox" name="is_followhost" class="Checkbox__input" {{ old('is_followhost') ? 'checked' : '' }}>
                                        <label for="is_followhost" class="Checkbox__label">Register as a Business</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('lat') || $errors->has('lng') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label class="col-md-4 control-label">Your Followout Location</label>

                                <div class="col-md-6">
                                    <input id="location" type="text" class="form-control" name="location" placeholder="Your Followout Location" onkeypress="return event.keyCode != 13;" value="{{ old('location') }}">
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

                            <div class="form-group {{ $errors->has('lat') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label class="col-md-4 control-label is-required">Followout Latitude</label>

                                <div class="col-md-6">
                                    <input id="lat" type="text" name="lat" value="{{ old('lat') ? old('lat') : '0' }}" class="form-control" readonly>

                                    @if ($errors->has('lat'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('lat') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('lng') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label class="col-md-4 control-label is-required">Followout Longitude</label>

                                <div class="col-md-6">
                                    <input id="lng" type="text" name="lng" value="{{ old('lng') ? old('lng') : '0' }}" class="form-control" readonly>

                                    @if ($errors->has('lng'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('lng') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('address') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label for="address" class="col-md-4 control-label">Followout Address</label>

                                <div class="col-md-6">
                                    <input id="address" type="text" class="form-control" name="address" placeholder="439 Karley Loaf Suite 897" value="{{ old('address') }}">

                                    @if ($errors->has('address'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('address') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('city') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label for="city" class="col-md-4 control-label">Followout City</label>

                                <div class="col-md-6">
                                    <input id="city" type="text" class="form-control" name="city" value="{{ old('city') }}" placeholder="San Francisco">

                                    @if ($errors->has('city'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('city') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('state') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label for="state" class="col-md-4 control-label">Followout State</label>

                                <div class="col-md-6">
                                    <input id="state" type="text" class="form-control" name="state" value="{{ old('state') }}" placeholder="California">

                                    @if ($errors->has('state'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('state') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('zip_code') ? 'has-error' : '' }}">
                                <label for="zip_code" class="col-md-4 control-label is-required">{{ (old('is_followhost')) ? 'Followout Zip': 'Zip' }}</label>

                                <div class="col-md-6">
                                    <input id="zip_code" type="text" class="form-control" name="zip_code" value="{{ old('zip_code') }}" placeholder="12345" required>

                                    @if ($errors->has('zip_code'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('zip_code') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('country_id') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label for="country_id" class="col-md-4 control-label">Followout Country</label>

                                <div class="col-md-6">
                                    <select id="country_id" class="selectize" name="country_id" data-live-search="true">
                                        <option value="">Select country...</option>
                                        @foreach ($data['countries'] as $country)
                                            @if (old('country_id'))
                                                <option value="{{ $country->id }}" data-data="{{ $country->toJson() }}" {{ old('country_id') == $country->id ? 'selected' : null }}>{{ $country->name }}</option>
                                            @else
                                                <option value="{{ $country->id }}" data-data="{{ $country->toJson() }}" {{ $data['countries_us']->id == $country->id ? 'selected' : null }}>{{ $country->name }}</option>
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

                            <div class="form-group {{ $errors->has('sales_rep') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                <label for="sales_rep" class="col-md-4 control-label">Sales Representative Code</label>

                                <div class="col-md-6">
                                    <input id="sales_rep" type="text" class="form-control" name="sales_rep" value="{{ old('sales_rep') }}" placeholder="Leave blank if you don't have the code">

                                    @if ($errors->has('sales_rep'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('sales_rep') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group {{ $errors->has('terms') ? 'has-error' : '' }}">
                                <div class="col-md-6 col-md-offset-4">
                                    <div class="Checkbox">
                                        <input id="terms" type="checkbox" name="terms" class="Checkbox__input" {{ old('terms') ? 'checked' : '' }}>
                                        <label for="terms" class="Checkbox__label">I understand and agree to the Followout LLC <a target="_blank" href="{{ route('about', ['#terms']) }}">Terms of Service</a> and <a target="_blank" href="{{ route('about', ['#privacy']) }}">Privacy Policy</a>.</label>

                                        @if ($errors->has('terms'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('terms') }}</strong>
                                            </span>
                                        @endif
                                    </div>
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

@push('scripts-footer')
    <script>
        $('#is_followhost').change(function() {
            toggleFollowhostForm();
        });

        function toggleFollowhostForm() {
            var $input = $("#is_followhost");

            if ($input.is(':checked')) {
                $('.followhost-group').show();
                $('.non-followhost-group').hide();

                $('#lat').attr('disabled', false);
                $('#lng').attr('disabled', false);

                $('label[for=name]').html('Company Name');
                $('#name').attr('placeholder', 'Example LLC');
                $('label[for=country_id-selectized]').addClass('is-required').attr('required', true);
                $('label[for=city]').addClass('is-required').attr('required', true);
                $('label[for=address]').addClass('is-required').attr('required', true);
                $('label[for=zip_code]').html('Followout Zip');

                if (map == null) {
                    initMap();
                }
            } else {
                $('label[for=name]').html('Name');
                $('#name').attr('placeholder', 'John Doe');
                $('label[for=country_id-selectized]').removeClass('is-required').attr('required', false);
                $('label[for=city]').removeClass('is-required').attr('required', false);
                $('label[for=address]').removeClass('is-required').attr('required', false);
                $('label[for=zip_code]').html('Zip');

                $('#lat').attr('disabled', true);
                $('#lng').attr('disabled', true);

                $('#is_followhost').removeAttr('checked');

                $('.non-followhost-group').show();
                $('.followhost-group').hide();
            }
        }

        $(function() {
            toggleFollowhostForm();
        });
    </script>

    @include('includes.google-maps-editable')
@endpush
