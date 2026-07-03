<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'position',
        'wip_limit',
        'is_done_column',
        'fallback_progress',
    ];

    protected function casts(): array
    {
        return [
            'is_done_column' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function isOverWipLimit(): bool
    {
        if (! $this->wip_limit) {
            return false;
        }

        return $this->tasks()->count() > $this->wip_limit;
    }
}
