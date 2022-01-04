@extends('layouts.app')

@section('page-title', 'Chats')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="Block Block--chat">
                    <div class="Block__body ChatWrap">
                        <div id="chat-content" class="col-md-8 col-md-push-4 ChatContent">
                            <div class="ChatEmptyText">
                                Select a chat to view message history.
                            </div>
                        </div>

                        <div class="col-md-4 col-md-pull-8 ChatListWrap">
                            @if ($chats->count() > 0)
                                <div class="ChatList">
                            @endif
                                @forelse ($chats as $user)
                                    @php
                                        $lastMessage = auth()->user()->getLastMessageFromChat($user->id);
                                    @endphp
                                    <div class="ChatListItem {{ auth()->user()->hasUnreadMessagesFromUser($user->id) ? 'ChatListItem--unread' : '' }} {{ $user->isOnline() ? 'ChatListItem--online' : '' }}" data-chat-id="{{ $user->id }}" data-url="{{ route('messages.chat', ['chat' => $user->id]) }}">
                                        <div class="ChatListItem__avatar">
                                            <img src="{{ $user->avatarURL() }}" alt="{{ $user->name }}">
                                        </div>
                                        <div class="ChatListItem__content">
                                            <div class="ChatListItem__title-and-date">
                                                <div class="ChatListItem__title">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="ChatListItem__date" data-timestamp="{{ $lastMessage->created_at->timestamp }}" data-moment-from-now></div>
                                            </div>
                                            <div class="ChatListItem__message">
                                                @if ($lastMessage->from->id === auth()->user()->id)
                                                    <span class="ChatListItem__message-from">You: </span>
                                                @endif
                                                {{ $lastMessage->message }}
                                            </div>
                                            @if (auth()->user()->hasUnreadMessagesFromUser($user->id))
                                                <div class="ChatListItem__unread">
                                                    {{ auth()->user()->unreadMessagesCountFromUser($user->id) }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="ChatEmptyText">
                                        You have no chats yet.
                                    </div>
                                @endforelse
                            @if ($chats->count() > 0)
                                </div>
                            @endif
                        </div>

                        <div id="chat-content-loader" style="display:none">
                            <div class="ChatEmptyText">
                                <i class="fas fa-fw fa-2x fa-spinner fa-pulse"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-footer')
    <script>
        var unreadMessagesCount = {{ auth()->user()->unreadMessagesCount() }};
        var chatLoading = null;

        refreshMomentDates();

        window.setInterval(function() {
            refreshMomentDates();
        }, 1000);

        $(document).on('click','.ChatListItem', function() {
            loadChat($(this));
        });

        function loadChat(chat) {
            if (chat.hasClass('ChatListItem--active')) {
                return false;
            }

            if (chatLoading != null) {
                chatLoading.abort();
                chatLoading = null;
            }

            $('.ChatListItem--active').removeClass('ChatListItem--active');

            chat.addClass('ChatListItem--active');

            chatLoading = $.ajax({
                url: chat.data('url'),
                beforeSend: function() {
                    var loader = $("#chat-content-loader").html();

                    $("#chat-content").html(loader);
                },
                success: function(response) {
                    $("#chat-content").html(response);

                    markUnreadMessagesAsRead(chat);

                    initChat();

                    initMessages();

                    refreshMomentDates();
                }
            });
        }

        function initChat() {
            scrollToLatestMessage();

            $('textarea.js-auto-size').textareaAutoSize().val('').trigger('input');
        }

        function initMessages() {
            $('[data-toggle="tooltip"]').tooltip();
        }

        function scrollToLatestMessage() {
            // TODO: scroll to "New messages" banner if exists

            $(".ChatContent__messages-wrap").scrollTop($(".ChatContent__messages-wrap")[0].scrollHeight);
        }

        function refreshMomentDates() {
            $('[data-moment-from-now]').each(function(index, el) {
                var element = $(el);

                var timeFromNow = moment.unix(element.attr('data-timestamp')).fromNow();

                element.html(timeFromNow);
            });
        }

        function markUnreadMessagesAsRead(chat) {
            if (chat.find('.ChatListItem__unread').first().length) {
                // Update chat list
                var unreadMessagesForChatCount = parseInt(chat.find('.ChatListItem__unread').first().html(), 10);
                chat.find('.ChatListItem__unread').remove();

                // Update counter
                unreadMessagesCount = unreadMessagesCount - unreadMessagesForChatCount;

                // Update header
                if (unreadMessagesCount === 0) {
                    $('.Header__nav-item .has-unread-messages-icon').remove();
                }
            }

            // Update chat
            setInterval(function() {
                $('.ChatMessage--mark-read').removeClass('ChatMessage--mark-read').removeClass('ChatMessage--unread');
            }, 3500);
        }

        $(window).on('resize', function() {
            if ($(this).width() >= 992) {
                var height = $(this).height() - 172;
            } else {
                var height = $(this).height() - 102;
            }

            $('.ChatWrap').css('height', height+'px');
        });

        window.dispatchEvent(new Event('resize'));
    </script>
@endpush
