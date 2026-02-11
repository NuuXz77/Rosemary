<?php

namespace App\Livewire\Admin\Users\Modals;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    public $username = '';
    public $password = '';
    public $is_active = true;
    public $role_id = null;

    protected function rules()
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'is_active' => 'boolean',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    protected $messages = [
        'username.required' => 'Username wajib diisi.',
        'username.unique' => 'Username sudah digunakan.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 6 karakter.',
    ];

    public function save()
    {
        $this->validate();

        try {
            $user = User::create([
                'username' => $this->username,
                'password' => $this->password,
                'is_active' => $this->is_active,
            ]);

            $role = Role::findOrFail($this->role_id);
            $user->assignRole($role->name);

            $this->reset(['username', 'password', 'is_active', 'role_id']);
            $this->is_active = true;
            $this->resetValidation();

            $this->dispatch('user-created');
            $this->dispatch('close-create-modal');

            session()->flash('success', "User '{$user->username}' berhasil ditambahkan!");
            $this->dispatch('show-toast', type: 'success', message: "User '{$user->username}' berhasil ditambahkan!");
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.modals.create', [
            'roles' => $roles,
        ]);
    }
}
