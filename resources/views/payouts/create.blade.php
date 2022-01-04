@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Create payout
                            </div>
                        </div>
                        <div class="Block__body">
                            <form class="Form form-horizontal" role="form" method="POST" action="{{ route('payouts.store') }}">
                                {{ csrf_field() }}

                                <div class="form-group {{ $errors->has('recipient') ? 'has-error' : '' }}">
                                    <label for="recipient" class="col-md-4 control-label is-required">Recipient</label>

                                    <div class="col-md-8">
                                        <select id="recipient" class="selectize-contact" name="recipient" required>
                                            @if (is_null(old('recipient')))
                                                <option value="">{{ 'Start typing...' }}</option>
                                            @else
                                                <option value="{{ old('recipient') }}">{{ old('recipient') }}</option>
                                            @endif
                                        </select>

                                        @if ($errors->has('recipient'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('recipient') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                                    <label for="amount" class="col-md-4 control-label is-required">Amount USD</label>

                                    <div class="col-md-8">
                                        <input id="amount" name="amount" type="text" class="form-control" value="{{ old('amount') ? old('amount') : number_format(0.00, 2, '.', '') }}" placeholder="0.00" autofocus required>

                                        @if ($errors->has('amount'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('amount') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('item_type') ? 'has-error' : '' }}">
                                    <label class="col-md-4 control-label is-required">Reason</label>

                                    <div class="col-md-8">
                                        <select id="item_type" class="selectize" name="item_type" required>
                                            @foreach ($itemTypes as $itemType)
                                                <option value="{{ $itemType->type }}" {{ old('item_type') == 'followee_services' ? 'selected' : null }}>{{ $itemType->name }}</option>
                                            @endforeach
                                            <option value="custom" {{ old('item_type') == 'other' ? 'selected' : null }}>Other</option>
                                        </select>

                                        @if ($errors->has('item_type'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('item_type') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group {{ $errors->has('notes') ? 'has-error' : '' }}">
                                    <label for="notes" class="col-md-4 control-label">Notes</label>

                                    <div class="col-md-8">
                                        <input id="notes" name="notes" type="text" class="form-control" value="{{ old('notes') }}" placeholder="Here you may specify the details of payout">

                                        @if ($errors->has('notes'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('notes') }}</strong>
                                            </span>
                                        @endif
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


@push('scripts-footer')
    <script>
        $(function(){
            var usersSelect = $("#recipient")[0].selectize;

            $.ajax({
                url: "{{ action('API\UsersController@getUsers') }}",
                cache: false,
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', "Bearer " + Laravel.api_token);
                },
                success: function(response) {
                    response.data.users.forEach(function(user) {
                        usersSelect.addOption({
                            name: user.name,
                            email: user.email,
                        });
                    });

                    usersSelect.refreshOptions(false);
                }
            });
        });
    </script>
@endpush
