<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'pause-reward-program-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Pause Reward Program</h4>
            </div>
            <div class="modal-body">
                <div>You are about to pause <strong>{{ $rewardProgram->title }}</strong> reward program.</div>
                <br>
                <div>The reward program will be hidden from the list of available programs for other users.</div>
                <br>
                <div>Users won't be able to redeem the rewards or request to present <strong>{{ $rewardProgram->followout->title }}</strong>.</div>
                <br>
                <div>Any pending invites to presenters won't be canceled, attendees gained by presenters will count towards their personal reward goals, so they will be able to redeem rewards later if you decide to resume the reward program.</div>
                <br>
                <div><strong>Warning:</strong> users could have started promoting the followout, proceed with caution.</div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('reward_programs.pause', ['rewardProgram' => $rewardProgram->getKey()]) }}" class="Button Button--sm Button--danger">Pause program</a>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
