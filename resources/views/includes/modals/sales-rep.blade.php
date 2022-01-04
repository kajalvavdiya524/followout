<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'sales-rep-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Sales Representative</h4>
            </div>
            <div class="modal-body">
                @if ($user->wasInvitedBySalesRep())
                    <div>
                        This user has the following sales representative {{ $user->wasInvitedBySalesRepWithPromo() ? 'promo' : '' }} code attached to his account: <strong>{{ $user->sales_rep_promo_code ?: $user->sales_rep_code }}</strong>
                    </div>

                    @if ($user->subscribed() && $user->subscription->isChargebeeSubscription())
                        <br>

                        <div>
                            <strong>Note:</strong>{{ $user->name }} is already subscribed via ChargeBee.
                        </div>
                    @endif
                @else
                    <form id="{{ isset($modalId) ? 'sales-rep-form-'.$modalId : 'sales-rep-form' }}" class="Form Form--modal form-horizontal" action="{{ route('users.manage.sales-rep', ['user' => $user->id]) }}" method="POST">
                        {{ csrf_field() }}

                        <div class="form-group form-group--last">
                            <label for="code" class="col-md-4 control-label">Sales Rep. Code</label>

                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control" name="code" placeholder="Code or promo code">
                            </div>
                        </div>
                    </form>
                @endif
            </div>
            <div class="modal-footer">
                @if (!$user->wasInvitedBySalesRep())
                    <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'sales-rep-form-'.$modalId : 'sales-rep-form' }}').submit();">
                        Apply code
                    </div>
                @endif
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
