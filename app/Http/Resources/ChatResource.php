<?php

namespace App\Http\Resources;

use App\Message;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // This is a User model, Chat model doesn't exist

        $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();

        $lastMessage = $authUser->getLastMessageFromChat($this->id);

        $unreadCount = $authUser->messages_received()->from($this->id)->unread()->count();

        return [
            'chat_id' => $this->id,
            'avatar_url' => $this->avatarURL(),
            'last_message' => new MessageResource(Message::find(optional($lastMessage)->id)),
            'unread_count' => $unreadCount,
        ];
    }
}
