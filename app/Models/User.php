<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, HasPushSubscriptions, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'share_token',
        'seen_onboarding',
        'timezone',
        'owner_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'seen_onboarding' => 'boolean',
        ];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function simpleTodos(): HasMany
    {
        return $this->hasMany(SimpleTodo::class);
    }

    public function displayTimezone(): string
    {
        return $this->timezone ?: config('kerjaku.display_timezone');
    }

    // ── Profile & share ────────────────────────────────────────────

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        return '';
    }

    public function generateShareToken(): string
    {
        $this->share_token = Str::random(16);
        $this->save();

        return $this->share_token;
    }

    public function getShareLinkAttribute(): string
    {
        return $this->share_token
            ? route('shared.todos', $this->share_token)
            : '';
    }

    // ── Staff / multi-user ────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function staffMembers(): HasMany
    {
        return $this->hasMany(User::class, 'owner_id');
    }

    public function staffInvitations(): HasMany
    {
        return $this->hasMany(StaffInvitation::class, 'owner_id');
    }

    /**
     * Projects a staff account has been individually assigned to. Empty for
     * owner accounts — they use projects() instead.
     */
    public function assignedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_staff');
    }

    public function isStaff(): bool
    {
        return $this->owner_id !== null;
    }

    public function isOwner(): bool
    {
        return $this->owner_id === null;
    }

    /**
     * The single source of truth for "which projects can this account see" —
     * an owner's own projects, or a staff member's assigned subset. Every
     * ownership check in the app should go through this (or canAccessProject)
     * rather than comparing project_id/user_id directly, so the staff model
     * doesn't have to be re-derived in a dozen places.
     */
    public function accessibleProjects(): Builder
    {
        if ($this->isStaff()) {
            return Project::query()->whereHas('staffMembers', fn ($q) => $q->whereKey($this->id));
        }

        return Project::query()->where('user_id', $this->id);
    }

    public function canAccessProject(Project $project): bool
    {
        if ($this->isStaff()) {
            return $this->assignedProjects()->whereKey($project->id)->exists();
        }

        return $project->user_id === $this->id;
    }

    /**
     * Structural/destructive actions (delete task, delete project, delete
     * kanban column, invite other staff) are owner-only — staff work inside
     * projects, they don't restructure them.
     */
    public function canManageProject(Project $project): bool
    {
        return $this->isOwner() && $project->user_id === $this->id;
    }

    public function canViewReports(): bool
    {
        return $this->isOwner();
    }
}
