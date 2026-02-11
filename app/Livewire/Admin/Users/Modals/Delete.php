<?php

namespace App\Livewire\Admin\Users\Modals;

use Livewire\Component;
use App\Models\User;

class Delete extends Component
{
    public $userId;
    public $username;
    public $is_active = true;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->username = $user->username;
        $this->is_active = (bool) $user->is_active;
    }

    public function delete()
    {
        try {
            $user = User::findOrFail($this->userId);

            if (auth()->id() === $user->id) {
                session()->flash('error', 'Anda tidak dapat menghapus akun sendiri.');
                $this->dispatch('show-toast', type: 'error', message: 'Anda tidak dapat menghapus akun sendiri.');
                return;
            }

            $username = $user->username;
            $user->delete();

            $this->dispatch('close-delete-modal');

            session()->flash('success', "User '{$username}' berhasil dihapus!");
            $this->dispatch('show-toast', type: 'success', message: "User '{$username}' berhasil dihapus!");
            $this->dispatch('user-deleted');

            $this->reset(['userId', 'username', 'is_active']);
            $this->is_active = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.users.modals.delete');
    }
}
