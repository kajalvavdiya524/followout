<div class="modal fade" id="delete-followout-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Delete Followout</h4>
            </div>
            <div class="modal-body">
                <div>You are about to delete <strong>{{ $followout->title }}</strong> followout.</div>
                @if ($followout->isReposted())
                    <br>
                    <div>Warning: you will be removed from the list of followees of the original followout.</div>
                    <br>
                    <div>Warning: reward program job progress will be reset.</div>
                @else
                    <br>
                    <div>Warning: all reward programs that are linked to this followout will be deleted.</div>
                @endif
                <br>
                <div class="text-bold">This cannot be undone.</div>
                <br>
                <div>Are you really sure about this?</div>
            </div>
            <div class="modal-footer">
                <div onclick="event.preventDefault(); document.getElementById('followout-delete-form').submit();" class="Button Button--sm Button--danger">Delete Followout</div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>

                <form id="followout-delete-form" action="{{ route('followouts.destroy', ['followout' => $followout->id]) }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                </form>
            </div>
        </div>
    </div>
</div>
