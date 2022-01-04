<div class="modal fade" id="followout-cant-be-public-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    @if (isset($reason) && $reason === 'has_public_followout')
                        Public followout in progress
                    @else
                        Subscription required
                    @endif
                </h4>
            </div>
            <div class="modal-body">
                @if (isset($reason) && $reason === 'has_public_followout')
                    @php
                        $publicFollowoutInProgress = auth()->user()->followouts()->ongoing()->public()->first();
                    @endphp
                    <div>
                        You already have a <a target="_blank" href="{{ route('followouts.show', ['followout' => $publicFollowoutInProgress->id]) }}">public followout</a> in progress. Please wait until it ends or delete it.
                    </div>
                    <br>
                    <div>
                        You can only have one public followout in progress.
                    </div>
                    <br>
                    <div>
                        You can post unlimited amount of followouts that are visible to <strong>Followout Community</strong> or <strong>Invite only</strong>.
                    </div>
                @else
                    @include('includes.subscription-benefits-list')
                @endif
            </div>
            <div class="modal-footer">
                @if (isset($reason) && $reason === 'has_public_followout')
                    <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
                @else
                    <a href="{{ route('pricing') }}" class="Button Button--sm Button--primary">Subscribe</a>
                    <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
                @endif
            </div>
        </div>
    </div>
</div>
