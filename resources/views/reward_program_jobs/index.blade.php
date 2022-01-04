@extends('layouts.app')

@section('content')
    <div class="Section Section--no-padding-mobile">
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    @if (auth()->user()->isFollowhost())
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    My Reward Programs <span class="text-danger" style="font-size: 13px;">* Customers must also present coupon</span>
                                </div>
                            </div>
                            <div class="Block__body">
                                <div class="table-responsive">
                                    <table class="table RewardProgramJobTable">
                                        <thead>
                                            <tr>
                                                <th>Followout</th>
                                                <th>Reward program</th>
                                                <th>Reward</th>
                                                <th class="text-center">FollowOut Count</th>
                                                <th>Followee</th>
                                                <th>Reward Redeemed</th>
                                                <th>Redeemed At</th>
                                                <th>Reward Not Received</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($claimedRewardProgramJobs as $rewardProgramJob)
                                                <tr>
                                                    <td style="width: 1px;">
                                                        <a href="{{ $rewardProgramJob->parent_followout->url() }}">
                                                            <img src="{{ $rewardProgramJob->parent_followout->flyerURL() }}" style="width: 100%: max-width: 50px;">
                                                        </a>
                                                    </td>
                                                    <td>{{ $rewardProgramJob->reward_program->title }}</td>
                                                    <td>{{ $rewardProgramJob->reward_program->description }}</td>
                                                    <td class="text-center">
                                                        @if ($rewardProgramJob->rewardIsReceived() || $rewardProgramJob->canBeRedeemed())
                                                            <span class="text-success">
                                                                {{ $rewardProgramJob->reward_program->redeem_count }} of {{ $rewardProgramJob->reward_program->redeem_count }}
                                                            </span>
                                                        @else
                                                            <span class="text-danger">
                                                                {{ $rewardProgramJob->getAvailableCheckinsCount() }} of {{ $rewardProgramJob->reward_program->redeem_count }}
                                                            </span>
                                                        @endif
                                                        @if ($rewardProgramJob->reward_program->require_coupon)
                                                            <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="{{ $rewardProgramJob->user->url() }}">{{ $rewardProgramJob->user->name }}</a>
                                                        @if ($rewardProgramJob->isPending())
                                                            <br>
                                                            <small class="text-muted">(Pending approval)</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($rewardProgramJob->isRedeemed())
                                                            <div class="text-bold text-success">Yes</div>
                                                        @else
                                                            <div class="text-bold text-danger">
                                                                No

                                                                @if ($rewardProgramJob->rewardIsReceived())
                                                                    <br>
                                                                    <small>(Marked as received)</small>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($rewardProgramJob->isRedeemed())
                                                            {{ $rewardProgramJob->redeemed_at->tz(session_tz())->format('m/d/Y h:i A') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($rewardProgramJob->inDispute())
                                                            <div class="text-bold text-danger">
                                                                Not received
                                                            </div>
                                                        @endif

                                                        @if ($rewardProgramJob->inDispute() && $rewardProgramJob->hostCanCloseDispute())
                                                            <div>
                                                                <a href="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}">Mark as received</a>
                                                            </div>
                                                        @elseif (!$rewardProgramJob->inDispute() && $rewardProgramJob->canBeRedeemed() && !$rewardProgramJob->rewardIsReceived())
                                                            <div>
                                                                <a href="{{ route('reward_program_jobs.receive', ['rewardProgramJob' => $rewardProgramJob->id]) }}">Mark as received</a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-muted">No redeemed jobs.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    My Reward Programs (Redeemed) <span class="text-danger" style="font-size: 13px;">* Customers must also present coupon</span>
                                </div>
                            </div>
                            <div class="Block__body">
                                <div class="table-responsive">
                                    <table class="table RewardProgramJobTable">
                                        <thead>
                                            <tr>
                                                <th>Followout</th>
                                                <th>Reward program</th>
                                                <th>Reward</th>
                                                <th>FollowOut Count</th>
                                                <th>Redeemed by</th>
                                                <th>Redeemed at</th>
                                                <th>Reward received</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($redeemedRewardProgramJobs as $rewardProgramJob)
                                                <tr>
                                                    <td style="width: 1px;">
                                                        <a href="{{ $rewardProgramJob->parent_followout->url() }}">
                                                            <img src="{{ $rewardProgramJob->parent_followout->flyerURL() }}" style="width: 100%: max-width: 50px;">
                                                        </a>
                                                    </td>
                                                    <td>{{ $rewardProgramJob->reward_program->title }}</td>
                                                    <td>{{ $rewardProgramJob->reward_program->description }}</td>
                                                    <td>
                                                        <span class="text-success">
                                                            {{ $rewardProgramJob->reward_program->redeem_count }} of {{ $rewardProgramJob->reward_program->redeem_count }}
                                                            @if ($rewardProgramJob->reward_program->require_coupon)
                                                                <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="{{ $rewardProgramJob->user->url() }}">{{ $rewardProgramJob->user->name }}</a>
                                                    </td>
                                                    <td>
                                                        {{ $rewardProgramJob->redeemed_at->tz(session_tz())->format(config('followouts.datetime_format')) }}
                                                    </td>
                                                    <td>
                                                        @if ($rewardProgramJob->inDispute())
                                                            <div class="text-danger text-bold">No</div>
                                                            @if ($rewardProgramJob->hostCanCloseDispute())
                                                                <div>
                                                                    <a href="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}">Mark as received</a>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="text-bold text-success">Yes</div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-muted">No redeemed jobs.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="Block">
                            <div class="Block__header">
                                <div class="Block__heading">
                                    My Reward Programs (Not yet redeemed)  <span class="text-danger" style="font-size: 13px;">* Customers must also present coupon</span>
                                </div>
                            </div>
                            <div class="Block__body">
                                <div class="table-responsive">
                                    <table class="table RewardProgramJobTable">
                                        <thead>
                                            <tr>
                                                <th>Followout</th>
                                                <th>Reward program</th>
                                                <th>Reward</th>
                                                <th>FollowOut Count</th>
                                                <th>Followee</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingRewardProgramJobs as $rewardProgramJob)
                                                <tr>
                                                    <td style="width: 1px;">
                                                        <a href="{{ $rewardProgramJob->parent_followout->url() }}">
                                                            <img src="{{ $rewardProgramJob->parent_followout->flyerURL() }}" style="width: 100%: max-width: 50px;">
                                                        </a>
                                                    </td>
                                                    <td>{{ $rewardProgramJob->reward_program->title }}</td>
                                                    <td>{{ $rewardProgramJob->reward_program->description }}</td>
                                                    <td>
                                                        <span class="{{ $rewardProgramJob->getAvailableCheckinsCount() >= $rewardProgramJob->reward_program->redeem_count ? 'text-success' : 'text-danger' }}">
                                                            {{ $rewardProgramJob->getAvailableCheckinsCount() }} of {{ $rewardProgramJob->reward_program->redeem_count }}
                                                        </span>
                                                        @if ($rewardProgramJob->reward_program->require_coupon)
                                                            <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a target="_blank" href="{{ $rewardProgramJob->user->url() }}">{{ $rewardProgramJob->user->name }}</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-muted">No pending jobs.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div> --}}
                    @endif

                    {{-- Reward programs available for all users to apply for --}}
                    <div class="Block">
                        <div class="Block__header">
                            <div class="Block__heading">
                                Reward Programs <span class="text-danger" style="font-size: 13px;">* Customers must also present coupon</span>
                            </div>
                        </div>
                        <div class="Block__body">
                            <div class="table-responsive">
                                <table class="table RewardProgramJobTable">
                                    <thead>
                                        <tr>
                                            <th>Followout</th>
                                            <th>Followhost</th>
                                            <th>Reward program</th>
                                            <th>Reward</th>
                                            <th class="text-center" style="line-height: 1;">FollowOut <br> COUNT REQUIRED <br> to receive <br> reward</th>
                                            <th class="text-center">Progress</th>
                                            <th>Redeem by date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($availableRewardPrograms as $rewardProgram)
                                            @php
                                                $rewardProgramJob = $rewardProgram->getJobByUser(auth()->user()->id);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <a href="{{ $rewardProgram->followout->url() }}">
                                                        <img src="{{ $rewardProgram->pictureURL() }}" width="50" />
                                                    </a>
                                                </td>
                                                <td><a target="_blank" href="{{ $rewardProgram->author->url() }}">{{ $rewardProgram->author->name }}</a></td>
                                                <td>{{ $rewardProgram->title }}</td>
                                                <td>
                                                    {{ $rewardProgram->description }}
                                                    @if ($rewardProgramJob && $rewardProgramJob->rewardIsReceived())
                                                        <span class="text-success">(Marked as received)</span>
                                                    @elseif ($rewardProgramJob && $rewardProgramJob->inDispute())
                                                        <span class="text-danger">(Marked as not received)</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="text-danger">{{ $rewardProgram->redeem_count }}</span>
                                                </td>
                                                <td class="text-center">
                                                    @if ($rewardProgram->claimedByUser(auth()->user()->id))
                                                        @if ($rewardProgram->redeemedByUser(auth()->user()->id))
                                                            <span class="text-success">
                                                                {{ $rewardProgram->redeem_count }} of {{ $rewardProgram->redeem_count }}
                                                            </span>

                                                            @if ($rewardProgram->require_coupon)
                                                                <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                            @endif
                                                        @else
                                                            <span class="{{ $rewardProgramJob->getAvailableCheckinsCount() >= $rewardProgram->redeem_count ? 'text-success' : 'text-danger' }}">
                                                                {{ $rewardProgramJob->getAvailableCheckinsCount() }} of {{ $rewardProgram->redeem_count }}
                                                            </span>

                                                            @if ($rewardProgram->require_coupon)
                                                                <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                            @endif
                                                        @endif
                                                    @else
                                                        0 of {{ $rewardProgram->redeem_count }}
                                                        @if ($rewardProgram->require_coupon)
                                                            <span class="text-danger text-bold" title="Only customers that present a coupon during checkin will be counted towards FollowOut count on reward program job.">*</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>{{ $rewardProgram->followout->ends_at->tz(session_tz())->format(config('followouts.datetime_format')) }}</td>
                                                <td>
                                                    @if ($rewardProgram->claimedByUser(auth()->user()->id))
                                                        @if ($rewardProgram->redeemedByUser(auth()->user()->id))
                                                            <div class="Button Button--success Button--disabled">Redeemed</div>
                                                        @elseif ($rewardProgram->canBeRedeemedByUser(auth()->user()->id))
                                                            @php
                                                                $modalId = 'redeem-reward-program-' . $rewardProgram->id;
                                                            @endphp
                                                            <div class="Button Button--danger" data-toggle="modal" data-target="#{{ $modalId }}">
                                                                Redeem
                                                            </div>
                                                            @push('modals')
                                                                @include('includes.modals.redeem-reward-program', ['modalId' => $modalId])
                                                            @endpush
                                                        @elseif (!$rewardProgram->canBeRedeemedByUser(auth()->user()->id))
                                                            <a target="_blank" href="{{ $rewardProgramJob->followout->url() }}" class="Button Button--default">In progress</a>
                                                        @endif
                                                    @elseif($rewardProgram->author->id === auth()->user()->id)
                                                        <div class="Button Button--default Button--disabled">This is your program</div>
                                                    @else
                                                        @if ($rewardProgram->followout->hasPendingFollowee(auth()->user()->id))
                                                            <a href="{{ $rewardProgram->followout->url() }}" class="Button Button--danger">Pending invite</a>
                                                        @elseif (Gate::allows('request-to-present-followout', $rewardProgram->followout))
                                                            @if (auth()->user()->hasOpenDisputes())
                                                                <a href="javascript:void(0);" class="Button Button--danger" data-toggle="modal" data-target="#resolve-pending-disputes-modal">
                                                                    Claim
                                                                </div>
                                                                @push('modals')
                                                                    @include('includes.modals.resolve-pending-disputes')
                                                                @endpush
                                                            @else
                                                                <a href="{{ route('followouts.present-request', ['reward_program_id' => $rewardProgram->id]) }}" class="Button Button--danger">
                                                                    Claim
                                                                </div>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </td>
                                                {{-- <td>
                                                    @if ($rewardProgramJob)
                                                        @if ($rewardProgramJob->inDispute())
                                                            <div class="text-danger text-bold">No</div>
                                                            @if ($rewardProgramJob->userCanCloseDispute())
                                                                <div>
                                                                    <a href="{{ route('reward_program_jobs.dispute.toggle', ['rewardProgramJob' => $rewardProgramJob->id]) }}">Cancel request</a>
                                                                </div>
                                                            @endif
                                                        @elseif (!$rewardProgramJob->rewardIsReceived())
                                                            <div class="text-danger text-bold">No</div>
                                                            @if ($rewardProgramJob->userCanCloseDispute())
                                                                <div>
                                                                    <a href="{{ route('reward_program_jobs.receive', ['rewardProgramJob' => $rewardProgramJob->id]) }}">Mark as received</a>
                                                                </div>
                                                            @endif
                                                        @elseif ($rewardProgramJob->isRedeemed() || $$rewardProgramJob->rewardIsReceived())
                                                            <div class="text-bold text-success">Yes</div>
                                                            @if ($rewardProgramJob->userCanOpenDispute())
                                                                <div>
                                                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#{{ 'open-job-' . $rewardProgramJob->id .'-dispute-modal' }}">Not received?</a>
                                                                </div>

                                                                @include('includes.modals.open-job-dispute', ['modalId' => 'open-job-' . $rewardProgramJob->id .'-dispute-modal'])
                                                            @endif
                                                        @endif
                                                    @endif
                                                </td> --}}
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-muted">No reward programs available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
