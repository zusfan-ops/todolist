<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public $avatar;
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public string $shareUrl = '';
    public int $todoCount = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $this->shareUrl = $user->share_link;
        $this->todoCount = $user->simpleTodos()->count();
    }

    public function updatedAvatar(): void
    {
        $this->validate([
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();

        if ($user->avatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $path = $this->avatar->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        $this->dispatch('toast', message: 'Foto profil diperbarui');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', Password::defaults(), 'confirmed:newPasswordConfirmation'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->newPassword),
        ]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->dispatch('toast', message: 'Kata sandi diperbarui');
    }

    public function generateShareLink(): void
    {
        $this->shareUrl = auth()->user()->generateShareToken();
        $this->dispatch('toast', message: 'Tautan berbagi dibuat');
    }

    public function revokeShareLink(): void
    {
        auth()->user()->update(['share_token' => null]);
        $this->shareUrl = '';
        $this->dispatch('toast', message: 'Tautan berbagi dinonaktifkan');
    }

    public function render()
    {
        return view('livewire.settings.index');
    }
}