<?php

namespace App\Livewire\Staff;

use App\Models\StaffInvitation;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $inviteName = '';

    public string $inviteEmail = '';

    public ?string $generatedLink = null;

    public function mount(): void
    {
        abort_if(auth()->user()->isStaff(), 403);
    }

    public function invite(): void
    {
        abort_if(auth()->user()->isStaff(), 403);

        $this->validate([
            'inviteName' => ['required', 'string', 'max:100'],
            'inviteEmail' => ['required', 'email', 'max:150', 'unique:users,email'],
        ]);

        $invitation = auth()->user()->staffInvitations()->create([
            'name' => $this->inviteName,
            'email' => $this->inviteEmail,
            'token' => StaffInvitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->generatedLink = route('invite.accept', $invitation->token);
        $this->reset(['inviteName', 'inviteEmail']);
    }

    public function toggleAssignment(int $staffId, int $projectId): void
    {
        $staff = auth()->user()->staffMembers()->findOrFail($staffId);
        $project = auth()->user()->projects()->findOrFail($projectId);

        if ($staff->assignedProjects()->whereKey($project->id)->exists()) {
            $staff->assignedProjects()->detach($project->id);
        } else {
            $staff->assignedProjects()->attach($project->id);
        }
    }

    public function removeStaff(int $staffId): void
    {
        $staff = auth()->user()->staffMembers()->findOrFail($staffId);
        $staff->delete();

        $this->dispatch('toast', message: 'Staf dihapus');
    }

    public function revokeInvitation(int $invitationId): void
    {
        auth()->user()->staffInvitations()->whereKey($invitationId)->whereNull('accepted_at')->firstOrFail()->delete();

        $this->dispatch('toast', message: 'Undangan dibatalkan');
    }

    public function render()
    {
        $staffMembers = auth()->user()->staffMembers()->with('assignedProjects')->orderBy('name')->get();
        $invitations = auth()->user()->staffInvitations()->whereNull('accepted_at')->where('expires_at', '>', now())->latest()->get();
        $projects = auth()->user()->projects()->active()->orderBy('position')->get();

        return view('livewire.staff.index', [
            'staffMembers' => $staffMembers,
            'invitations' => $invitations,
            'projects' => $projects,
        ]);
    }
}
