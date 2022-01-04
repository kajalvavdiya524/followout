<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'manage-subscription-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Manage subscription</h4>
            </div>
            <div class="modal-body">
                @if ($user->subscribed())
                    @if ($user->subscription->isChargebeeSubscription())
                        <div>
                            <strong>{{ $user->name }} is already subscribed via ChargeBee.</strong>
                        </div>
                    @else
                        <div>
                            <strong>{{ $user->name }} has been given a subscription that is still active.</strong>
                        </div>
                    @endif
                    <br>
                    <div>
                        The subscription will end at {{ $user->subscription->expires_at->tz(session_tz())->format(config('followouts.date_format_date_time_string')) }}
                    </div>
                    @if ($user->subscription->isChargebeeSubscription())
                        <br>
                        <div class="text-muted">Since {{ $user->name }} is subscribed via ChargeBee, their subscription will be automatically extended.</div>
                    @endif
                @else
                    <form id="{{ isset($modalId) ? 'manage-subscription-form-'.$modalId : 'manage-subscription-form' }}" class="Form Form--modal form-horizontal" action="{{ route('users.manage.give-subscription') }}" method="POST">
                        {{ csrf_field() }}
                        <input type="text" name="user_id" value="{{ $user->id }}" hidden>

                        <div class="form-group">
                            <label for="followout_id" class="col-md-4 control-label">Subscription type</label>

                            <div class="col-md-6">
                                <select id="followout_id" class="selectize" name="subscription_type" required>
                                    <option value="">Select a subscription...</option>
                                    @php
                                        $subscriptions = \App\Product::subscriptions()->get();
                                    @endphp
                                    @foreach ($subscriptions as $product)
                                        <option value="{{ $product->type }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group form-group--last">
                            <label for="followout_id" class="col-md-4 control-label">Subscription multiplier</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="subscription_count" min="1" placeholder="Enter an integer value" value="1">
                                <div class="text-muted" style="margin-top: 10px;">
                                    By default user will receive Followouts Pro account for one month (monthly plan) or one year (yearly plan).
                                    <br>
                                    <br>
                                    However, you can modify the amount of months/years that will be given to user by entering an integer value above 1.
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                @if ($user->subscribed())
                    <form id="remove-subscription-form" action="{{ route('users.manage.remove-subscription') }}" method="POST" style="display: none;">
                        {{ csrf_field() }}
                        <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    </form>
                    <script>
                        function confirmSubscriptionRemoval() {
                            var result = confirm('Are you really sure you want to remove {{ $user->name }} subscription?');

                            if (result) {
                                return document.getElementById('remove-subscription-form').submit();
                            }
                        }
                    </script>
                    <div class="Button Button--sm Button--danger" onclick="confirmSubscriptionRemoval();">
                        Remove subscription
                    </div>
                @else
                    <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'manage-subscription-form-'.$modalId : 'manage-subscription-form' }}').submit();">
                        Give subscription
                    </div>
                @endif
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
