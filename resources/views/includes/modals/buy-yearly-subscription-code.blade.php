<div class="modal fade" id="buy-yearly-subscription-code-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Buy subscription code (1 year)</h4>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="loader-ripple">
                        <div></div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts-footer')
    <script>
        $(document).on('shown.bs.modal', '#buy-yearly-subscription-code-modal', function (e) {
            var modal = $(this);
            var modalBody = modal.find('.modal-body');

            if (modalBody.find('iframe').length) return false;

            $.ajax({
                url: '{{ route('subscription-code.iframe') }}',
                type: 'POST',
                data: {
                    plan_id: 'followouts-pro-yearly',
                },
                success: function(response) {
                    var disclaimer = $('<p>').addClass('text-center text-muted').text('Disclaimer: subscription begins on the date of purchase and will not renew automatically.');
                    var iframe = $('<iframe src="' + response.url + '" width="100%" height="782"></iframe>');

                    modalBody.empty().append(disclaimer).append(iframe);
                },
                error: function(jqXHR) {
                    var response = jqXHR.responseJSON;

                    modal.modal('hide');

                    modalBody.empty().append($('<div class="text-center"><div class="loader-ripple"><div></div><div></div></div></div>'));

                    toastr.error(response.message);
                },
            });
        });
    </script>
@endpush
