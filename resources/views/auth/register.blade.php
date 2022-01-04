@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                @if ($data['plan'] === 'monthly' || $data['plan'] === 'annual' || $data['plan'] === 'basic')
                    <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                        <div style="margin-bottom: 15px;">
                            <img src="/img/price-intro.jpg" class="img-responsive" style="max-height: 110px;">
                        </div>
                    </div>
                @endif
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Register
                            </div>
                            @if ($data['plan'] === 'monthly')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['monthlySubscription']->price, 2) }} / mo.
                                    </span>
                                    <br>
                                    <span class="text-muted">billed monthly, cancel anytime</span>
                                </div>
                            @elseif ($data['plan'] === 'annual')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['yearlySubscription']->price / 12, 2) }} / mo.
                                    </span>
                                    <br>
                                    <span class="text-muted">billed annually, cancel anytime</span>
                                </div>
                            @elseif ($data['plan'] === 'basic')
                                <div class="Block__header-misc-info Block__header-misc-info--two-lines">
                                    <span class="text-bold">
                                        ${{ number_format($appData['basicSubscription']->price, 2) }}
                                    </span>
                                    <br>
                                    <span class="text-muted">billed only once</span>
                                </div>
                            @endif
                        </div>
                        <div class="Block__body">
                            <form class="Form Form--block-padding form-horizontal" method="POST" action="{{ route('register') }}" autocomplete="off">
                                {{ csrf_field() }}

                                @if (request()->filled('aa_token'))
                                    <input type="hidden" name="account_activation_token" value="{{ request()->input('aa_token') }}">
                                @endif

                                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                                    <label for="name" class="col-md-4 control-label is-required">Name</label>

                                    <div class="col-md-6">
                                        <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="John Doe" required>

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
                                        <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="johndoe@example.com" required>

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
                                    <label for="password" class="col-md-4 control-label is-required">Password</label>

                                    <div class="col-md-6">
                                        <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">

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

                                @if ($data['plan'] !== 'monthly' && $data['plan'] !== 'annual' && $data['plan'] !== 'basic')
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
                                @endif

                                <div class="form-group {{ $errors->has('is_followhost') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Become a Followhost</label>

                                    <div class="col-md-6">
                                        @if ($data['plan'] !== 'free' || Route::currentRouteName() === 'register.business')
                                            <div class="Checkbox Checkbox--disabled">
                                                <input id="is_followhost" type="checkbox" name="is_followhost" class="Checkbox__input" checked>
                                                <label class="Checkbox__label">Register as a Business</label>
                                            </div>
                                            <input type="hidden" name="plan" value="{{ $data['plan'] }}">
                                        @else
                                            <div class="Checkbox">
                                                <input id="is_followhost" type="checkbox" name="is_followhost" class="Checkbox__input" {{ old('is_followhost') ? 'checked' : '' }}>
                                                <label for="is_followhost" class="Checkbox__label">Register as a Business</label>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if ($data['plan'] === 'free')
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
                                @endif

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

                                <div class="form-group {{ $errors->has('subscription_code') ? 'has-error' : '' }} followhost-group" style="{{ (old('is_followhost')) ? null : 'display: none;' }}">
                                    <label for="subscription_code" class="col-md-4 control-label">Subscription Code</label>

                                    <div class="col-md-6">
                                        <input id="subscription_code" type="text" class="form-control" name="subscription_code" value="{{ old('subscription_code') ?? request()->input('subscription_code', old('subscription_code')) }}" placeholder="Leave blank if you don't have the code">

                                        @if ($errors->has('subscription_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('subscription_code') }}</strong>
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

                                <div class="form-group non-followhost-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <div class="Checkbox">
                                            <input id="autosubcribe_to_followhosts" type="checkbox" name="autosubcribe_to_followhosts" class="Checkbox__input" {{ $errors->any() && !old('autosubcribe_to_followhosts') ? '' : 'checked' }}>
                                            <label for="autosubcribe_to_followhosts" class="Checkbox__label">Automatically subscribe to businesses to receive deals or offers</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <div class="Checkbox">
                                            <input id="available_for_promotion" type="checkbox" name="available_for_promotion" class="Checkbox__input" {{ old('available_for_promotion') ? 'checked' : '' }}>
                                            <label for="available_for_promotion" class="Checkbox__label">I can help promote businesses</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            {{ $data['plan'] !== 'free' ? 'Proceed to payment' : 'Register' }}
                                        </button>
                                    </div>
                                </div>

                                {{--
                                <hr class="non-followhost-group">

                                <div class="form-group non-followhost-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <div class="text-muted" style="padding: 0 0 10px 0;">
                                            Or simply register with a social network:
                                        </div>


                                        <div class="Button Button--facebook" onclick="registerViaFacebook('{{ route('login.facebook') }}');">
                                            <i class="fab fa-fw fa-facebook-f"></i>
                                            Register with Facebook
                                        </div>
                                    </div>
                                </div>
                                --}}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-footer')
    @if ($data['plan'] === 'free')
        @include('includes.google-maps-editable')
    @endif

    <script>
        function registerViaFacebook(url) {
            var $terms = $("#terms");

            if (!$terms.is(':checked')) {
                toastr.error('Please agree to our Terms of Service first.');
                return false;
            }

            window.location.replace(url);
        }

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

                @if ($data['plan'] !== 'monthly' && $data['plan'] !== 'annual' && $data['plan'] !== 'basic')
                    if (map == null) {
                        initMap();
                    }
                @endif
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
@endpush
