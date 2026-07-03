<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskPhotoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'type' => $this->type,
            'url' => $this->url,
            'thumb_url' => $this->thumb_url,
            'caption' => $this->caption,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'taken_at' => $this->taken_at?->toIso8601String(),
        ];
    }
}
