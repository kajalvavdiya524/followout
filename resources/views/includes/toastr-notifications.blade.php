@if (session()->has('toastr.error'))
    @if (is_array(session()->get('toastr.error')))
        <script> toastr.error("{!! session()->get('toastr.error.message') !!}", "{!! session()->get('toastr.error.title') !!}") </script>
    @else
        <script> toastr.error("{!! session()->get('toastr.error') !!}") </script>
    @endif
@endif

@if (session()->has('toastr.success'))
    @if (is_array(session()->get('toastr.success')))
        <script> toastr.success("{!! session()->get('toastr.success.message') !!}", "{!! session()->get('toastr.success.title') !!}") </script>
    @else
        <script> toastr.success("{!! session()->get('toastr.success') !!}") </script>
    @endif
@endif

@if (session()->has('toastr.warning'))
    @if (is_array(session()->get('toastr.warning')))
        <script> toastr.warning("{!! session()->get('toastr.warning.message') !!}", "{!! session()->get('toastr.warning.title') !!}") </script>
    @else
        <script> toastr.warning("{!! session()->get('toastr.warning') !!}") </script>
    @endif
@endif

@if (session()->has('toastr.info'))
    @if (is_array(session()->get('toastr.info')))
        <script> toastr.info("{!! session()->get('toastr.info.message') !!}", "{!! session()->get('toastr.info.title') !!}") </script>
    @else
        <script> toastr.info("{!! session()->get('toastr.info') !!}") </script>
    @endif
@endif
