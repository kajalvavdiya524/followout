<div class="modal fade" id="delete-user-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Delete user</h4>
            </div>
            <div class="modal-body">
                <div>
                    You are about to delete <strong>{{ $user->name }}</strong> <span class="text-muted">({{ $user->email }})</span>.
                </div>
                <br>
                <div class="text-bold">
                    This user account will be permanently deactivated.
                </div>
                <br>
                <div class="text-bold">
                    This cannot be undone.
                </div>
                <br>
                <div class="text-bold">
                    Are you really sure about this?
                </div>
            </div>
            <div class="modal-footer">
                <div onclick="event.preventDefault(); document.getElementById('delete-user-form').submit();" class="Button Button--sm Button--danger">Deactivate user</div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>

                <form id="delete-user-form" action="{{ route('users.destroy', ['user' => $user->id]) }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                </form>
            </div>
        </div>
    </div>
</div>
