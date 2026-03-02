<?php

namespace App\Livewire\Admin\StudentGroupMembers\Modals;

use App\Models\StudentGroupMembers;
use App\Models\StudentGroups;
use App\Models\Students;
use Livewire\Component;

class Create extends Component
{
    public $student_group_id = '';
    public $student_id = '';

    protected $rules = [
        'student_group_id' => 'required|exists:student_groups,id',
        'student_id' => 'required|exists:students,id',
    ];

    protected $messages = [
        'student_group_id.required' => 'Kelompok wajib dipilih',
        'student_id.required' => 'Siswa wajib dipilih',
    ];

    public function save()
    {
        $this->validate();

        // Check if already a member
        $exists = StudentGroupMembers::where('student_group_id', $this->student_group_id)
            ->where('student_id', $this->student_id)
            ->exists();

        if ($exists) {
            $this->dispatch('show-toast', type: 'error', message: 'Siswa sudah menjadi anggota kelompok ini!');
            return;
        }

        try {
            $member = StudentGroupMembers::create([
                'student_group_id' => $this->student_group_id,
                'student_id' => $this->student_id,
            ]);

            $this->reset(['student_group_id', 'student_id']);
            $this->resetValidation();

            $this->dispatch('close-create-modal');
            $this->dispatch('show-toast', type: 'success', message: "Berhasil menambahkan anggota kelompok!");
            $this->dispatch('member-created');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menambahkan anggota: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.student-group-members.modals.create', [
            'groups' => StudentGroups::where('status', true)->orderBy('name')->get(),
            'students' => Students::where('status', true)->orderBy('name')->get(),
        ]);
    }
}
