<div class="ChatContent__wrap">
    <div class="ChatContent__messages-wrap">
        @php
            $newMessagesFrom = null;
        @endphp
        @foreach ($messages as $message)
            @if (is_null($newMessagesFrom) && $message->isUnread() && $message->to->id === auth()->user()->id)
                @php
                    $newMessagesFrom = $message->id;
                @endphp
                <div class="ChatNewMessagesBanner">
                    New messages
                </div>
            @endif
            @include('messages.message')
        @endforeach
    </div>

    <div class="ChatMessageFormWrap">
        <form id="message-form" class="Form form-horizontal ChatMessageForm" action="{{ action('API\MessagesController@send', ['user' => $chatId]) }}" method="POST">
            <div class="ChatMessageForm__input-wrap">
                <textarea id="message" class="form-control js-auto-size" name="message" required maxlength="10000" placeholder="Write a message..."></textarea>
            </div>
            <div class="ChatMessageForm__send-button">
                <button type="submit" class="Button Button--sm Button--primary">
                    <i class="fas fa-fw fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $('#message').keypress(function(e) {
        // Enter was pressed without shift key
        if (e.which == 13 && !e.shiftKey) {
            e.preventDefault();

            if (!$.trim($(this).val())) {
                return false;
            }

            $('#message-form').submit();
        }
    });

    $('#message-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ action('API\MessagesController@send', ['user' => $chatId]) }}",
            type:'POST',
            data: {
                message: $("#message").val(),
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Authorization', "Bearer " + Laravel.api_token);

                $("#message").val('').trigger('input');

                $('#message-form').find('button').blur();
            },
            error: function(jqXHR) {
                var response = jqXHR.responseJSON;

                toastr.error(response.message);
            },
            success: function(response) {
                var chat = $('[data-chat-id="'+response.data.message.to_id+'"]').first();
                var message = response.data.view;

                $('.ChatContent__messages-wrap').append(message);

                chat.find('.ChatListItem__date').first().attr('data-timestamp', response.data.message.created_at_timestamp);
                chat.find('.ChatListItem__message').html('<span class="ChatListItem__message-from">You: </span> '+response.data.message.message);

                initMessages();

                scrollToLatestMessage();

                refreshMomentDates();

                markUnreadMessagesAsRead(chat);
            }
        });
    });
</script>
