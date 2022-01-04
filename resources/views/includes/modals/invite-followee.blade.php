@php
    $followouts = auth()->user()->followouts()->ongoingOrUpcoming()->get();
    $followouts = $followouts->reject(function ($followout, $key) use ($user) {
        return $followout->hasFollowee($user->id);
    });
@endphp

@if ($followouts->isEmpty())
    <div class="modal fade" id="{{ isset($modalId) ? $modalId : 'invite-followee-modal' }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Invite to Present</h4>
                </div>
                <div class="modal-body">
                    <div><strong>You have no upcoming followouts available.</strong></div>
                    <br>
                    <div>Create a Followout to send invites.</div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('followouts.create') }}" class="Button Button--sm Button--danger">
                        Create followout
                    </a>
                    <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="modal fade" id="{{ isset($modalId) ? $modalId : 'invite-followee-modal' }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Invite to Present Followout</h4>
                </div>
                <div class="modal-body">
                    <blockquote>
                        Hello.
                        <br>
                        <br>
                        You have been invited to present my Followout. If interested, please click the link below for more details.
                        <br>
                        <br>
                        Or contact me at your earliest convenience: {{ auth()->user()->email }}{{ auth()->user()->phone_number ? ', '. auth()->user()->phone_number : null }}
                    </blockquote>
                    <div>Message with this text will be sent to this Followee.</div>
                    <hr>
                    <form id="{{ isset($modalId) ? 'invite-followee-form-' . $modalId : 'invite-followee-form' }}" class="Form Form--modal form-horizontal" action="{{ route('followouts.invite') }}" method="POST">
                        @csrf

                        <input type="text" name="user_id" value="{{ $user->id }}" hidden>

                        @if (auth()->user()->isFollowhost())
                            <div class="form-group form-group--last">
                                <label for="reward_program_id" class="col-md-4 control-label is-required">Reward Program</label>

                                <div class="col-md-8">
                                    <select id="reward_program_id" class="selectize" name="reward_program_id" required>
                                        <option value="">Select reward program and followout</option>
                                        @foreach (auth()->user()->getActiveRewardPrograms() as $rewardProgram)
                                            <option value="{{ $rewardProgram->id }}">{{ $rewardProgram->title }} ({{ $rewardProgram->followout->title }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="form-group form-group--last">
                                <label for="followout_id" class="col-md-4 control-label is-required">Followout</label>

                                <div class="col-md-6">
                                    <select id="followout_id" class="selectize" name="followout_id" required>
                                        <option value="">Select your followout</option>
                                        @foreach ($followouts as $followout)
                                            <option value="{{ $followout->id }}">{{ $followout->title }} {{ $followout->isReposted() ? '(reposted)' : null }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'invite-followee-form-' . $modalId : 'invite-followee-form' }}').submit();">
                        Invite
                    </div>
                    <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
