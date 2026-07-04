<?php

namespace App\Livewire\Staff;

use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class AcceptInvite extends Component
{
    public string $token;

    public ?StaffInvitation $invitation = null;

    public string $password = '';

    public string $password_confirmation = '';

    public bool $invalid = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $invitation = StaffInvitation::where('token', $token)->first();

        if (! $invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            $this->invalid = true;

            return;
        }

        $this->invitation = $invitation;
    }

    public function accept(): void
    {
        abort_if($this->invalid || ! $this->invitation, 404);

        $this->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $invitation = StaffInvitation::whereKey($this->invitation->id)->first();

        if (! $invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            $this->invalid = true;

            return;
        }

        $user = User::create([
            'owner_id' => $invitation->owner_id,
            'name' => $invitation->name,
            'email' => $invitation->email,
            'password' => Hash::make($this->password),
            'timezone' => 'Asia/Makassar',
        ]);

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirectRoute('today', navigate: true);
    }

    public function render()
    {
        return view('livewire.staff.accept-invite');
    }
}
