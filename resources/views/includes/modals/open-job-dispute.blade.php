@php
    /** @var \App\RewardProgramJob $rewardProgramJob */
@endphp

<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'open-job-dispute-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Reward Not Received Notification</h4>
            </div>
            <div class="modal-body">
                <blockquote>
                    Hello.
                    <br>
                    <br>
                    Please contact me at {{ $rewardProgramJob->user->email }} with details on how to receive the reward ({{ $rewardProgramJob->reward_program->description }}).
                    <br>
                    <br>
                    Thank you.
                </blockquote>
                <div>Message with this text will be sent to this Followhost.</div>
                <form id="{{ isset($modalId) ? 'open-job-dispute-modal-form-' . $modalId : 'open-job-dispute-modal-form' }}" class="Form Form--modal form-horizontal" action="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}" method="GET"></form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'open-job-dispute-modal-form-' . $modalId : 'open-job-dispute-modal-form' }}').submit();">
                    Send notification
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
