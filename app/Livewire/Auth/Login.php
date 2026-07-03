<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], true)) {
            $this->addError('email', 'Email atau kata sandi salah.');

            return;
        }

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        $this->redirectRoute('today', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
