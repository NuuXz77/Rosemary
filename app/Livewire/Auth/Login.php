<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public $username;
    public $password;
    public $remember = false;

    public $success = null;
    public $error = null;

    #[Title('Login')]
    public function render()
    {
        return view('livewire.auth.login');
    }

    public function login()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $this->username)->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            $message = 'Username atau password salah!';
            $this->error = $message;
            $this->dispatch('show-toast', type: 'error', message: $message);
            $this->password = '';
            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $user->update([
            'terakhir_login' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        $this->success = 'Login berhasil! Mengalihkan...';
        $this->dispatch('show-toast', type: 'success', message: $this->success);

        if ($user->hasRole('Cashier')) {
            return $this->redirect(route('kasir.pos'), navigate: true);
        }

        return $this->redirect('/dashboard', navigate: true);
    }
}
