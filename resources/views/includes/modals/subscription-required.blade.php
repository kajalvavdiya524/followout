<div class="modal fade" id="subscription-required-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    Subscription required
                </h4>
            </div>
            <div class="modal-body">
                @include('includes.subscription-benefits-list')
            </div>
            <div class="modal-footer">
                <a href="{{ route('pricing') }}" class="Button Button--sm Button--primary">
                    Subscribe
                </a>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
