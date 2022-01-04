<div class="modal fade" id="north-star-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Create</h4>
            </div>
            <div class="modal-body">
                <div class="ButtonRow">
                    <a href="{{ route('followouts.create-manually') }}" class="Button Button--sm Button--danger">
                        Create FollowOut
                    </a>
                    <div class="ButtonRow__or"></div>
                    <a href="{{ route('followouts.create') }}" class="Button Button--sm Button--danger">
                        Post FollowOut
                    </a>
                    <div class="ButtonRow__or"></div>
                    @if (Gate::allows('manage-reward-programs'))
                        @if (auth()->user()->hasOpenDisputes())
                            <a class="Button Button--danger" href="#" data-toggle="modal" data-target="#resolve-pending-disputes-modal" onclick="$('#north-star-modal').modal('hide');">
                                Create Reward Program
                            </a>
                            @push('modals')
                                @include('includes.modals.resolve-pending-disputes')
                            @endpush
                        @else
                            <a class="Button Button--danger" href="{{ route('reward_programs.create') }}">
                                Create Reward Program
                            </a>
                        @endif
                    @else
                        <a class="Button Button--danger" data-toggle="modal" data-target="#subscription-required-modal" onclick="$('#north-star-modal').modal('hide');">
                            Create Reward Program
                        </a>
                    @endif
                    @if (auth()->user()->isFollowhost())
                        <div class="ButtonRow__or"></div>
                        <a href="{{ route('coupons.create') }}" class="Button Button--sm Button--primary" style="font-size: 11px;">
                            Create GEO Coupons, Deals, Offers
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
