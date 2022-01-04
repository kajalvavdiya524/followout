<div class="modal fade" id="invite-friends-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Invite Friends</h4>
            </div>
            <div class="modal-body">
                <form id="invite-friends-modal-form" class="Form Form--modal form-horizontal" action="{{ route('followouts.invite-friends', ['followout' => $followout->id]) }}" method="POST">
                    {{ csrf_field() }}

                    <blockquote>
                        Hello.
                        <br>
                        <br>
                        You have been invited to attend {{ $followout->title }}. If interested, please click the link below for more details.
                        <br>
                        <br>
                        Thanks,
                        <br>
                        {{ auth()->user()->name }}
                    </blockquote>

                    <div>Message with this text will be sent to specified emails.</div>

                    <hr>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="col-md-4 control-label">
                            Emails
                        </label>

                        <div id="invite-friends-modal-form-invites" class="col-md-6">
                            <input type="email" name="invites[]" class="form-control" placeholder="Enter email" style="margin-bottom: 10px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-8 col-md-offset-4">
                            <div id="invite-friends-modal-form-add-invitee-btn" class="Button Button--sm Button--danger">
                                Add email
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('invite-friends-modal-form').submit();">
                    Invite
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="invite-friends-modal-form-add-invitee-template" class="hidden">
    <input type="email" name="invites[]" class="form-control" placeholder="Enter email" style="margin-bottom: 10px;">
</div>
