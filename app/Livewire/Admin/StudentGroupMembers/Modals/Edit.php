<?php

namespace App\Livewire\Admin\StudentGroupMembers\Modals;

use App\Models\StudentGroupMembers;
use App\Models\StudentGroups;
use App\Models\Students;
use Livewire\Component;

class Edit extends Component
{
    public $memberId;
    public $student_group_id = '';
    public $student_id = '';

    protected $listeners = ['open-edit-modal' => 'loadMember'];

    protected function rules()
    {
        return [
            'student_group_id' => 'required|exists:student_groups,id',
            'student_id' => 'required|exists:students,id',
        ];
    }

    protected $messages = [
        'student_group_id.required' => 'Kelompok wajib dipilih',
        'student_id.required' => 'Siswa wajib dipilih',
    ];

    public function loadMember($id)
    {
        $this->memberId = $id;
        $member = StudentGroupMembers::findOrFail($id);
        
        $this->student_group_id = $member->student_group_id;
        $this->student_id = $member->student_id;
        
        $this->resetValidation();
    }

    public function update()
    {
        $this->validate();

        // Check if already a member in another record
        $exists = StudentGroupMembers::where('student_group_id', $this->student_group_id)
            ->where('student_id', $this->student_id)
            ->where('id', '!=', $this->memberId)
            ->exists();

        if ($exists) {
            $this->dispatch('show-toast', type: 'error', message: 'Siswa sudah menjadi anggota kelompok ini!');
            return;
        }

        try {
            $member = StudentGroupMembers::findOrFail($this->memberId);
            
            $member->update([
                'student_group_id' => $this->student_group_id,
                'student_id' => $this->student_id,
            ]);

            $this->dispatch('close-edit-modal');
            $this->dispatch('show-toast', type: 'success', message: "Data anggota kelompok berhasil diperbarui!");
            $this->dispatch('member-updated');

            $this->reset(['memberId', 'student_group_id', 'student_id']);
            $this->resetValidation();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.student-group-members.modals.edit', [
            'groups' => StudentGroups::where('status', true)->orderBy('name')->get(),
            'students' => Students::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
