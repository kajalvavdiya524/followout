<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();

        return [
            '_id' => $this->id,
            'from_id' => $this->from_id,
            'from' => $this->when($this->from_id !== null, new UserResource($this->whenLoaded('from'))),
            'to_id' => $this->to_id,
            'to' => $this->when($this->from_id !== null, new UserResource($this->whenLoaded('to'))),
            'message' => $this->message,
            'read_at' => $this->isRead() ? (string) $this->read_at : null,
            'created_at' => (string) $this->created_at,
            'created_at_timestamp' => $this->created_at->timestamp,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
