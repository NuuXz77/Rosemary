<?php

namespace App\Livewire\Admin\Users\Modals;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\User;
use Spatie\Permission\Models\Role;

class Edit extends Component
{
    public $userId;
    public $username = '';
    public $password = '';
    public $is_active = true;
    public $role_id = null;

    protected function rules()
    {
        return [
            'username' => 'required|string|max:50|unique:users,username,' . $this->userId,
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'role_id' => 'required|exists:roles,id',
        ];
    }

    protected $messages = [
        'username.required' => 'Username wajib diisi.',
        'username.unique' => 'Username sudah digunakan.',
        'password.min' => 'Password minimal 6 karakter.',
    ];

    #[On('open-edit-modal')]
    public function loadUser($id)
    {
        $this->userId = $id;
        $user = User::findOrFail($id);

        $this->username = $user->username;
        $this->is_active = (bool) $user->is_active;
        $this->password = '';
        $this->role_id = $user->roles()->pluck('id')->first();

        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        try {
            $user = User::findOrFail($this->userId);

            $data = [
                'username' => $this->username,
                'is_active' => $this->is_active,
            ];

            if (!empty($this->password)) {
                $data['password'] = $this->password;
            }

            $user->update($data);

            $role = Role::findOrFail($this->role_id);
            $user->syncRoles([$role->name]);

            $this->dispatch('user-updated');
            $this->dispatch('close-edit-modal');

            $this->dispatch('show-toast',
                type: 'success',
                message: "User '{$user->username}' berhasil diperbarui!"
            );

            $this->reset(['userId', 'username', 'password', 'is_active', 'role_id']);
            $this->is_active = true;
        } catch (\Exception $e) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Gagal memperbarui data: ' . $e->getMessage()
            );
        }
    }

    public function render()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get();

        return view('livewire.admin.users.modals.edit', [
            'roles' => $roles,
        ]);
    }
}
