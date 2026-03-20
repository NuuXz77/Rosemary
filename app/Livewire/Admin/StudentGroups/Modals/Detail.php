<?php

namespace App\Livewire\Admin\StudentGroups\Modals;

use App\Models\StudentGroups;
use App\Models\Students;
use Livewire\Component;

class Detail extends Component
{
    public $groupId;
    public $groupTitle = '';
    public $availableStudents = [];
    public $selectedStudents = [];

    protected $listeners = ['open-detail-modal' => 'loadGroupMembers'];

    public function loadGroupMembers($id): void
    {
        $group = StudentGroups::findOrFail($id);

        $this->groupId = $group->id;
        $this->groupTitle = $group->name;

        $this->availableStudents = Students::where('class_id', $group->class_id)
            ->where('status', true)
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->selectedStudents = $group->students()
            ->pluck('students.id')
            ->map(fn($studentId) => (string) $studentId)
            ->toArray();
    }

    public function save(): void
    {
        $group = StudentGroups::findOrFail($this->groupId);

        $pivotData = array_fill_keys($this->selectedStudents, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $group->students()->sync($pivotData);

        $this->dispatch('close-detail-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Anggota kelompok berhasil diperbarui.');
        $this->dispatch('group-members-updated');

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['groupId', 'groupTitle', 'availableStudents', 'selectedStudents']);
    }

    public function render()
    {
        return view('livewire.admin.student-groups.modals.detail');
    }
}
