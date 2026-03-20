<?php

namespace App\Livewire\Admin\StudentGroups\Modals;

use App\Models\Classes;
use App\Models\StudentGroups;
use Livewire\Component;

class Edit extends Component
{
    public $groupId;
    public $name = '';
    public $class_id = '';
    public $status = true;

    protected $listeners = ['open-edit-modal' => 'loadGroup'];

    protected $rules = [
        'name' => 'required|string|max:255',
        'class_id' => 'required|exists:classes,id',
        'status' => 'required|boolean',
    ];

    protected $messages = [
        'name.required' => 'Nama kelompok wajib diisi.',
        'class_id.required' => 'Kelas wajib dipilih.',
    ];

    public function loadGroup($id): void
    {
        $group = StudentGroups::findOrFail($id);

        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->class_id = $group->class_id;
        $this->status = (bool) $group->status;
    }

    public function update(): void
    {
        $this->validate();

        $group = StudentGroups::findOrFail($this->groupId);
        $group->update([
            'name' => $this->name,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-edit-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil diperbarui.');
        $this->dispatch('group-updated');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['groupId', 'name', 'class_id']);
        $this->status = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.student-groups.modals.edit', [
            'classes' => Classes::where('status', true)->whereHas('students')->get(),
        ]);
    }
}
