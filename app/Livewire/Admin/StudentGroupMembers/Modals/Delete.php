<?php

namespace App\Livewire\Admin\StudentGroupMembers\Modals;

use App\Models\StudentGroupMembers;
use Livewire\Component;

class Delete extends Component
{
    public $memberId;
    public $studentName;
    public $groupName;

    protected $listeners = ['confirm-delete' => 'loadMember'];

    public function loadMember($id)
    {
        $this->memberId = $id;
        $member = StudentGroupMembers::with(['student', 'studentGroup'])->findOrFail($id);
        
        $this->studentName = $member->student->name ?? '-';
        $this->groupName = $member->studentGroup->name ?? '-';
    }

    public function delete()
    {
        try {
            $member = StudentGroupMembers::findOrFail($this->memberId);
            $member->delete();

            $this->dispatch('close-delete-modal');
            $this->dispatch('show-toast', type: 'success', message: "Anggota kelompok berhasil dihapus!");
            $this->dispatch('member-deleted');

            $this->reset(['memberId', 'studentName', 'groupName']);
        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.student-group-members.modals.delete');
    }
}
