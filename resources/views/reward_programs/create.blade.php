@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                New Reward Program
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('reward_programs.store') }}" enctype="multipart/form-data">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('picture') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label">Picture</label>

                                    <div class="col-md-6">
                                        <div class="ImageInputWithPreview">
                                            <div data-for="picture" class="ImageInputWithPreview__picture ImageInputWithPreview__picture--picture"></div>
                                            <input id="picture" class="ImageInputWithPreview__input" type="file" name="picture" accept="image/x-png,image/jpeg">
                                        </div>
                                        <div class="ImageInputWithPreview__help-text">100x100px minimum</div>

                                        @if ($errors->has('picture'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('picture') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('title') ? 'has-error' : '' }}">
                                    <label for="title" class="col-md-4 control-label is-required">Reward program name</label>

                                    <div class="col-md-8">
                                        <input id="title" name="title" class="form-control" required value="{{ old('title') }}" placeholder="Promote my Followout">

                                        @if ($errors->has('title'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('title') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                                    <label for="description" class="col-md-4 control-label is-required">Reward</label>

                                    <div class="col-md-8">
                                        <input id="description" name="description" class="form-control" required value="{{ old('description') }}" placeholder="30% off all drinks and food">

                                        @if ($errors->has('description'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('description') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('redeem_count') ? 'has-error' : '' }}">
                                    <label for="redeem_count" class="col-md-4 control-label is-required">Reward Redeem Count</label>

                                    <div class="col-md-8">
                                        <input id="redeem_count" name="redeem_count" class="form-control" value="{{ old('redeem_count') }}" placeholder="How many checkins needed to reedem the reward" required>

                                        @if ($errors->has('redeem_count'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('redeem_count') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('redeem_code') ? 'has-error' : '' }}">
                                    <label for="redeem_code" class="col-md-4 control-label is-required">Reward Redeem Code</label>

                                    <div class="col-md-8">
                                        <input id="redeem_code" name="redeem_code" class="form-control" value="{{ old('redeem_code') }}" placeholder="Secret code that user needs to enter to reedem the reward" maxlength="128" required>

                                        @if ($errors->has('redeem_code'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('redeem_code') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Redeem by date</label>

                                    <div class="col-md-8">
                                        <input class="form-control" placeholder="Same as followout ending date" disabled>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="followout_id" class="col-md-4 control-label is-required">Attach to Followout</label>

                                    <div class="col-md-8">
                                        <select id="followout_id" class="selectize" name="followout_id" required>
                                            @forelse ($followouts as $followout)
                                                <option value="{{ $followout->id }}">{{ $followout->title }}</option>
                                            @empty
                                                <option value="">No followouts available</option>
                                            @endforelse
                                        </select>

                                        @if ($errors->has('followout_id'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('followout_id') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="enabled" class="col-md-4 control-label is-required">Status</label>

                                    <div class="col-md-8">
                                        <select id="enabled" class="selectize" name="enabled" required>
                                            <option value="1" {{ is_null(old('enabled')) || old('enabled') == '1' ? 'selected' : null }}>Active</option>
                                            <option value="0" {{ old('enabled') == '0' ? 'selected' : null }}>Paused</option>
                                        </select>

                                        <div class="text-muted" style="margin-top: 10px;">
                                            Only active programs are visible to users and users can be invited to promote reward program Followout.
                                        </div>

                                        @if ($errors->has('enabled'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('enabled') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Require coupon</label>

                                    <div class="col-md-8">
                                        <div class="Checkbox">
                                            <input id="require_coupon" type="checkbox" class="Checkbox__input" name="require_coupon">
                                            <label for="require_coupon" class="Checkbox__label">Customers must also present coupon</label>
                                        </div>
                                        <div class="text-muted" style="margin-top: 10px;">
                                            Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Auto accept consecutive claims</label>

                                    <div class="col-md-8">
                                        <div class="Checkbox Checkbox--disabled">
                                            <input id="auto_accept" type="checkbox" class="Checkbox__input" name="auto_accept" checked>
                                            <label for="auto_accept" class="Checkbox__label">Automatically accept job claim if user has previously claimed the job and reposted selected Followout from other reward program</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="Button Button--danger">
                                            Save
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
