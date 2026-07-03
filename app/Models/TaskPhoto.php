<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPhoto extends Model
{
    protected $fillable = [
        'task_id',
        'type',
        'disk',
        'path',
        'thumb_path',
        'caption',
        'sha256',
        'size_bytes',
        'latitude',
        'longitude',
        'taken_at',
        'client_uuid',
    ];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->path);
    }

    public function getThumbUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk($this->disk)->url($this->thumb_path);
    }
}
