<div class="modal fade" id="contact-support-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Contact support</h4>
            </div>
            <div class="modal-body">
                <div class="text-muted">Feel free to contact our support team if you have any questions.</div>
                <br>
                <form id="contact-support-form" class="Form Form--modal form-horizontal" action="{{ route('support.contact') }}" method="POST">
                    @csrf

                    @if (auth()->guest())
                        {{-- Name --}}
                        <div class="form-group">
                            <label for="contact-support-from-name" class="col-md-4 control-label">Your Name</label>
                            <div class="col-md-6">
                                <input id="contact-support-from-name" type="text" name="from_name" class="form-control" maxlength="128">
                            </div>
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="contact-support-from-email" class="col-md-4 control-label is-required">Your Email</label>
                            <div class="col-md-6">
                                <input id="contact-support-from-email" type="email" name="from_email" class="form-control" required>
                            </div>
                        </div>
                    @endif

                    {{-- Subject --}}
                    <div class="form-group">
                        <label for="contact-support-subject" class="col-md-4 control-label">Subject</label>
                        <div class="col-md-6">
                            <input id="contact-support-subject" type="text" name="subject" class="form-control" maxlength="128" value="{{ $subject ?? '' }}">
                        </div>
                    </div>

                    {{-- Message --}}
                    <div class="form-group">
                        <label for="contact-support-message" class="col-md-4 control-label is-required">Your Message</label>

                        <div class="col-md-6">
                            <textarea id="contact-support-message" name="message" class="form-control" rows="10" maxlength="10000" required></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div id="submit-contact-support-form-btn" class="Button Button--sm Button--danger">
                    Send message
                </div>
                <button type="button" class="Button Button--sm Button--default" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts-footer')
    <script>
        $(document).on('click', '#submit-contact-support-form-btn', function(e) {
            if ($('#contact-support-from-email').length && !$('#contact-support-from-email').val()) {
                toastr.error('Email is required.');
                return false;
            }

            if (!$('#contact-support-message').val()) {
                toastr.error('Message is required.');
                return false;
            }

            $('#contact-support-form').submit();
        });
    </script>
@endpush
