<?php

namespace App\Http\Controllers\API;

use Carbon;
use Validator;
use App\User;
use App\Message;
use App\Http\Resources\ChatResource;
use App\Http\Resources\ChatCollection;
use App\Http\Resources\MessageResource;
use App\Http\Resources\MessageCollection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessagesController extends Controller
{
    public function chats()
    {
        $authUser = auth()->guard('api')->user();

        // Get User IDs of those with whom auth user has a chat
        $messagesSentIds = $authUser->messages_sent()->get()->pluck('to_id')->all();
        $messagesReceivedIds = $authUser->messages_received()->get()->pluck('from_id')->all();
        $userIds = array_unique(array_merge($messagesSentIds, $messagesReceivedIds));

        $chats = User::whereIn('_id', $userIds)->get();

        // Order by last message date
        $chats = $chats->sortByDesc(function ($chat, $key) use ($authUser) {
            return $chat->getLastMessageFromChat($authUser->id)->timestamp;
        });

        $chats = new ChatCollection(ChatResource::collection($chats));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'chats' => $chats,
            ],
        ]);
    }

    public function chat($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($authUser->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $messagesSent = $authUser->messages_sent()->to($user->id)->get();
        $messagesReceived = $authUser->messages_received()->from($user->id)->get();
        $messageIds = $messagesSent->merge($messagesReceived)->pluck('id');

        $messages = new MessageCollection(
            MessageResource::collection(
                Message::whereIn('_id', $messageIds)->orderBy('created_at')->get()
            )
        );

        foreach ($authUser->messages_received()->from($user->id)->unread()->get() as $message) {
            $message->markAsRead();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'messages' => $messages,
            ],
        ]);
    }

    public function send(Request $request, $user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->blocked($authUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You\'ve been blocked by'.$user->name.'.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first('message'),
            ], 422);
        }

        $message = new Message;
        $message->from()->associate($authUser);
        $message->to()->associate($user);
        $message->message = $request->input('message');
        $message->read_at = null;
        $message->save();

        $view = view('messages.message', compact('message'))->render();

        $user->notify(new \App\Notifications\NewMessage($message));

        foreach ($authUser->messages_received()->from($user->id)->unread()->get() as $message) {
            $message->markAsRead();
        }

        $message = new MessageResource(Message::find($message->id));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'message' => $message,
                'view' => $view,
            ],
        ]);
    }
}
