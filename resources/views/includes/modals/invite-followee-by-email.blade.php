<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'invite-followee-by-email-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Invite followee</h4>
            </div>
            <div class="modal-body">
                <blockquote>
                    Hello.
                    <br>
                    <br>
                    You have been invited to present my Followout. If interested, please click the link below for more details.
                    <br>
                    <br>
                    Or contact me at your earliest convenience: {{ auth()->user()->email }}{{ auth()->user()->phone_number ? ', '.auth()->user()->phone_number : null }}
                </blockquote>
                <div>Message with this text will be sent to this email.</div>
                <hr>
                <form id="{{ isset($modalId) ? 'invite-followee-by-email-form-'.$modalId : 'invite-followee-by-email-form' }}" class="Form Form--modal form-horizontal" action="{{ route('followouts.invite-by-email') }}" method="POST">
                    {{ csrf_field() }}
                    <input type="text" name="followout_id" value="{{ $followout->id }}" hidden>

                    <div class="form-group form-group--last">
                        <label for="email" class="col-md-4 control-label is-required">Email</label>

                        <div class="col-md-6">
                            <input type="text" name="email" class="form-control" placeholder="Staff, promoter, influencer email..." required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'invite-followee-by-email-form-'.$modalId : 'invite-followee-by-email-form' }}').submit();">
                    Invite
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
