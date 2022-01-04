<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'present-followout-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Present Followout Request</h4>
            </div>
            <div class="modal-body">
                <blockquote>
                    Hello.
                    <br>
                    <br>
                    I would like the opportunity to present {{ $followout->title }}. If interested, please click the link below to review my profile.
                    <br>
                    <br>
                    Or contact me at your earliest convenience: {{ auth()->user()->email }}{{ auth()->user()->phone_number ? ', '.auth()->user()->phone_number : null }}
                </blockquote>
                <div>Message with this text will be sent to author of this Followout.</div>

                <form id="{{ isset($modalId) ? 'present-followout-form-' . $modalId : 'present-followout-form' }}" class="Form Form--modal form-horizontal" action="{{ route('followouts.present-request') }}" method="GET">
                    @csrf
                    <div class="form-group form-group--last">
                        <label for="reward_program_id" class="col-md-4 control-label is-required">Reward Program</label>

                        <div class="col-md-8">
                            <select id="reward_program_id" class="selectize" name="reward_program_id" required>
                                <option value="">Select reward program and followout</option>
                                @foreach (auth()->user()->getAvailableRewardProgramsForFollowout($followout->id) as $rewardProgram)
                                    <option value="{{ $rewardProgram->id }}">{{ $rewardProgram->title }} ({{ $rewardProgram->followout->title }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'present-followout-form-' . $modalId : 'present-followout-form' }}').submit();">
                    Send request
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
