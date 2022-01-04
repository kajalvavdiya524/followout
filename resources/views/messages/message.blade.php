<div class="ChatMessage {{ $message->isUnread() ? 'ChatMessage--unread' : '' }} {{ $message->isUnread() && $message->to->id === auth()->user()->id ? 'ChatMessage--mark-read' : '' }}">
    <div class="ChatMessage__wrap">
        <a href="{{ route('users.show', ['user' => $message->from->id]) }}" class="ChatMessage__avatar">
            <img src="{{ $message->from->avatarURL() }}" alt="{{ $message->from->name }}">
        </a>
        <div class="ChatMessage__message-wrap">
            <div class="ChatMessage__username-wrap">
                <a href="{{ route('users.show', ['user' => $message->from->id]) }}" class="ChatMessage__username">
                    {{ $message->from->name }}
                </a>
                <span class="ChatMessage__date" data-timestamp="{{ $message->created_at->timestamp }}" data-toggle="tooltip" data-placement="bottom" title="{{ $message->created_at->tz(session_tz())->format(config('followouts.date_format_time_at_date_string_short')) }}" data-moment-from-now></span>
            </div>
            <div class="ChatMessage__message">
                {!! nl2br(e($message->message)) !!}
            </div>
        </div>
    </div>
</div>
