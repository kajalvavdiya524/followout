<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'followee-intro-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Introduce Myself</h4>
            </div>
            <div class="modal-body">
                @if (auth()->check() && auth()->user()->isPrivate())
                    <div><strong>Your profile is private.</strong></div>
                    <br>
                    <div>Please set your profile to public.</div>
                @else
                    <blockquote>
                        Hello.
                        <br>
                        <br>
                        I would like to introduce myself. If interested, please click link below to review my profile.
                        <br>
                        <br>
                        Or contact me at your earliest convenience: {{ auth()->user()->email }}{{ auth()->user()->phone_number ? ', '.auth()->user()->phone_number : null }}
                    </blockquote>
                    <div>Message with this text will be sent to this Followhost.</div>
                @endif
            </div>
            <div class="modal-footer">
                @if (auth()->check() && auth()->user()->isPrivate())
                    <a href="{{ route('users.edit', ['user' => auth()->user()->id]) }}" class="Button Button--sm Button--danger">Enhance profile</a>
                @else
                    <a href="{{ route('users.followee-intro', ['user' => $user->id]) }}" class="Button Button--sm Button--danger">Send request</a>
                @endif
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
