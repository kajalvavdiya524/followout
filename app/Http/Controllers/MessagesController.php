<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Resources\ChatResource;
use App\Http\Resources\ChatCollection;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function chats()
    {
        // Get User IDs of those with whom auth user has a chat
        $messagesSentIds = auth()->user()->messages_sent()->get()->pluck('to_id')->all();
        $messagesReceivedIds = auth()->user()->messages_received()->get()->pluck('from_id')->all();
        $userIds = array_unique(array_merge($messagesSentIds, $messagesReceivedIds));

        $users = User::whereIn('_id', $userIds)->get();

        // Order by last message date
        $chats = $users->sortByDesc(function ($user, $key) {
            return $user->getLastMessageFromChat(auth()->user()->id)->created_at->timestamp;
        });

        return view('messages.chats', compact('chats'));
    }

    public function chat($chatId)
    {
        if (!(auth()->user()->messages_sent()->to($chatId)->first() || auth()->user()->messages_received()->from($chatId)->first())) {
            return abort(404);
        }

        // Get User IDs of those with whom auth user has a chat
        $messagesSent = auth()->user()->messages_sent()->to($chatId)->get();
        $messagesReceived = auth()->user()->messages_received()->from($chatId)->get();
        $messages = $messagesSent->merge($messagesReceived);
        $messages = $messages->sortBy('created_at');

        foreach (auth()->user()->messages_received()->from($chatId)->unread()->get() as $message) {
            $message->markAsRead();
        }

        return view('messages.chat', compact('chatId', 'messages'));
    }
}
