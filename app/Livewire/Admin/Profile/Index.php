<?php

namespace App\Livewire\Admin\Profile;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Profil Saya')]

    // View state
    public $isEditing = false;
    public $isChangingPassword = false;

    // Data
    public $name;
    public $username;
    public $email;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;
    public string $activityFilter = '';

    public function mount()
    {
        $this->refreshUserData();
    }

    public function refreshUserData()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if (!$this->isEditing) {
            $this->refreshUserData(); // Reset changes if cancelled
            $this->resetValidation();
        }
    }

    public function toggleChangePassword()
    {
        $this->isChangingPassword = !$this->isChangingPassword;
        if (!$this->isChangingPassword) {
            $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
            $this->resetValidation();
        }
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ]);

        $this->isEditing = false;
        $this->dispatch('show-toast', type: 'success', message: 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->update([
            'password' => bcrypt($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->isChangingPassword = false;

        $this->dispatch('show-toast', type: 'success', message: 'Password berhasil diperbarui.');
    }

    public function resetFilters(): void
    {
        $this->activityFilter = '';
    }

    public function render()
    {
        return view('livewire.admin.profile.index', [
            'user' => Auth::user(),
        ]);
    }
}
