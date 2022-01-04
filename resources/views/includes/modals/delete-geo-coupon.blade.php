<div class="modal fade" id="{{ isset($modalId) ? $modalId : 'delete-geo-coupon-modal' }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Delete GEO Coupon</h4>
            </div>
            <div class="modal-body">
                <div>You are about to delete <strong>{{ $coupon->title }}</strong> GEO coupon.</div>
                <br>
                @if ($coupon->followout)
                    <div><strong>Warning:</strong> if you delete this coupon, followout created from this coupon will also be removed.</div>
                @endif
                <div><strong>Warning:</strong> if you delete this coupon, it will also be removed from all Followouts it was linked to and all coupon statistics will be deleted as a result of this.</div>
                <form id="{{ isset($modalId) ? 'delete-geo-coupon-form-'.$modalId : 'delete-geo-coupon-form' }}" class="Form Form--hidden" action="{{ route('coupons.destroy', ['coupon' => $coupon->id]) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                </form>
            </div>
            <div class="modal-footer">
                <div class="Button Button--sm Button--danger" onclick="event.preventDefault(); document.getElementById('{{ isset($modalId) ? 'delete-geo-coupon-form-'.$modalId : 'delete-geo-coupon-form' }}').submit();">Delete coupon</div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
