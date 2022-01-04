<div class="modal fade" id="resolve-pending-disputes-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    Resolve open transactions
                </h4>
            </div>
            <div class="modal-body">
                <div class="text-danger text-bold">Resolve open transactions to resume:</div>
                <ul>
                    @if (auth()->user()->hasOpenDisputesAsFollowhost())
                        <li>One or more users have not received the reward from your reward program jobs.</li>
                    @endif
                    @if (auth()->user()->hasOpenDisputesAsFollowee())
                        <li>You have not received one or more rewards from reward program jobs.</li>
                    @endif
                </ul>

                <div>
                    Warning: resolving all transactions cannot be reversed.
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('reward_program_jobs.disputes.resolve-all') }}" class="Button Button--sm Button--danger">
                    Mark all as resolved
                </a>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
