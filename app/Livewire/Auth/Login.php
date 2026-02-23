<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

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
            'username' => 'required|min:3|max:30',
            'password' => 'required|min:3|max:30',
        ]);

        $credentials = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        if (Auth::attempt($credentials, $this->remember)) {
            $user = Auth::user();

            // Update last login
            $user->update([
                'terakhir_login' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            session()->regenerate();

            $this->success = 'Login berhasil! Mengalihkan...';

            // Redirect based on roles
            if ($user->hasRole('Admin')) {
                return $this->redirect('/dashboard', navigate: true);
            } elseif ($user->hasRole('Production')) {
                return $this->redirect('/productions', navigate: true);
            } elseif ($user->hasRole('Inventory')) {
                return $this->redirect('/material-stocks', navigate: true);
            } elseif ($user->hasRole('Cashier')) {
                return $this->redirect('/sales/pos', navigate: true);
            }

            // Fallback
            return $this->redirect('/dashboard', navigate: true);
        }

        $this->error = 'Username atau password salah!';
        $this->password = '';
    }
}
