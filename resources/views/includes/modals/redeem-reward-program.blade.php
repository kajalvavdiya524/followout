@php
    /** @var \App\RewardProgramJob $rewardProgramJob */
@endphp
<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'redeem-reward-program-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Redeem reward</h4>
            </div>
            <div class="modal-body">
                <form id="{{ isset($modalId) ? 'redeem-reward-program-form-' . $modalId : 'redeem-reward-program-form' }}" class="Form Form--modal form-horizontal" action="{{ route('reward_program_jobs.redeem', ['rewardProgramJob' => $rewardProgramJob->getKey()]) }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <div style="margin: 0 auto;" class="w-50">
                            <img src="{{ $rewardProgram->pictureURL() }}" alt="{{ $rewardProgram->title }}" class="img-responsive">
                        </div>
                        <h4 class="text-center">{{ $rewardProgram->title }}</h4>
                        <div class="text-center"><strong>Reward:</strong> {{ $rewardProgram->description }}</div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="email" class="col-md-4 control-label is-required">Code</label>

                        <div class="col-md-8">
                            <input type="text" name="code" class="form-control" placeholder="Code confirms you have received the reward" required>
                        </div>
                    </div>

                    <div class="form-group form-group--last">
                        <label for="email" class="col-md-4 control-label">Reward received?</label>

                        <div class="col-md-6">
                            @if ($rewardProgramJob->userCanOpenDispute() && $rewardProgramJob->rewardIsReceived())
                                <a class="Button Button--sm Button--danger" href="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}">
                                    Mark not received
                                </a>
                            @elseif ($rewardProgramJob->inDispute() && $rewardProgramJob->userCanCloseDispute())
                                <a class="Button Button--sm Button--danger" href="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}">
                                    Mark as received
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'redeem-reward-program-form-' . $modalId : 'redeem-reward-program-form' }}').submit();">
                    Reward redeemed
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
