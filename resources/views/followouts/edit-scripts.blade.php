<script>
    $(document).on('change', '#flyer', function(e) {
        // Check whether browser fully supports all File API
        if (window.File && window.FileReader && window.FileList && window.Blob) {
            if ($('#flyer').val()) {
                // Get the file size and file type from file input field
                var fsize = $('#flyer')[0].files[0].size;

                // If file size more than 100 MB (104857600 bytes)
                if (fsize > 104857600) {
                    toastr.error('Flyer file size is too big. Please select a different flyer.');
                }

                console.log('Flyer size: ' + fsize + ' bytes');
            }
        }
    });
</script>

@if (!$followout->isDefault())
    @if ($followout->based_on_followhost)
        @include('includes.google-maps')
    @else
        @include('includes.google-maps-editable')
    @endif

    <script> $(function() { initMap() }); </script>

    <script>
        $('#is_virtual').change(function() {
            toggleVirtualAddressForm();
        });

        function toggleVirtualAddressForm() {
            var $input = $("#is_virtual");

            if ($input.is(':checked')) {
                $('.virtual-group').show();
                $('.non-virtual-group').hide();

                $('#address').attr('required', false);
                $('#city').attr('required', false);
                $('#zip_code').attr('required', false);
                $('#lat').attr('disabled', true);
                $('#lng').attr('disabled', true);
            } else {
                $('#address').attr('required', true);
                $('#city').attr('required', true);
                $('#zip_code').attr('required', true);
                $('#lat').attr('disabled', false);
                $('#lng').attr('disabled', false);

                $('#is_virtual').removeAttr('checked');

                $('.virtual-group').hide();
                $('.non-virtual-group').show();
            }
        }

        $(function() {
            toggleVirtualAddressForm();
        });
    </script>
@endif
