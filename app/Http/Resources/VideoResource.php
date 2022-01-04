<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
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
            'processed_at' => $this->isProcessed() ? (string) $this->processed_at : null,
            'file_id' => $this->file_id,
            'file' => new FileResource($this->whenLoaded('file')),
            'uploader_id' => $this->uploader_id,
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'url' => $this->url('thumb'),
            'url_mp4' => $this->url('mp4'),
            'url_m3u8' => $this->url('m3u8'),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
