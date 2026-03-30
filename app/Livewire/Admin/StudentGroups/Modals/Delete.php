<?php

namespace App\Livewire\Admin\StudentGroups\Modals;

use App\Models\StudentGroups;
use Livewire\Component;

class Delete extends Component
{
    public $groupId;
    public $name = '-';
    public $className = '-';
    public $membersCount = 0;

    protected $listeners = ['confirm-delete' => 'loadDelete'];

    public function loadDelete($id): void
    {
        $group = StudentGroups::with(['schoolClass'])->findOrFail($id);

        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->className = $group->schoolClass->name ?? '-';
        $this->membersCount = $group->members()->count();
    }

    public function delete(): void
    {
        $group = StudentGroups::findOrFail($this->groupId);

        if ($group->members()->count() > 0 || $group->schedules()->count() > 0 || $group->productions()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Kelompok tidak bisa dihapus karena masih digunakan dalam jadwal, produksi, atau sudah memiliki anggota.');
            $this->dispatch('close-delete-modal');
            return;
        }

        $group->delete();

        $this->dispatch('close-delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil dihapus.');
        $this->dispatch('group-deleted');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['groupId', 'membersCount']);
        $this->name = '-';
        $this->className = '-';
    }

    public function render()
    {
        return view('livewire.admin.student-groups.modals.delete');
    }
}
