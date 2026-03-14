<?php

namespace App\Livewire\Admin\StudentGroups;

use App\Models\StudentGroups;
use App\Models\Classes;
use App\Models\Students;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Kelompok Siswa')]

    // Search & Pagination
    public string $search = '';
    public int $perPage = 10;

    // Form Properties
    public $groupId;
    public $name;
    public $class_id;
    public $status = true;
    public $isEdit = false;

    // Member Management Properties
    public $manageGroupId;
    public $manageGroupTitle = '';
    public $availableStudents = [];
    public $selectedStudents = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'class_id' => 'required|exists:classes,id',
        'status' => 'required|boolean',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['name', 'class_id', 'status', 'groupId', 'isEdit']);
        $this->status = true;
        $this->resetValidation();
        $this->resetMembers();
    }

    public function resetMembers()
    {
        $this->reset(['manageGroupId', 'manageGroupTitle', 'availableStudents', 'selectedStudents']);
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'group-modal');
    }

    public function store()
    {
        $this->validate();

        StudentGroups::create([
            'name' => $this->name,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-modal', id: 'group-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil ditambahkan.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $this->resetFields();
        $group = StudentGroups::findOrFail($id);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->class_id = $group->class_id;
        $this->status = (bool) $group->status;
        $this->isEdit = true;

        $this->dispatch('open-modal', id: 'group-modal');
    }

    public function update()
    {
        $this->validate();

        $group = StudentGroups::findOrFail($this->groupId);
        $group->update([
            'name' => $this->name,
            'class_id' => $this->class_id,
            'status' => $this->status,
        ]);

        $this->dispatch('close-modal', id: 'group-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil diperbarui.');
        $this->resetFields();
    }

    public function manageMembers($id)
    {
        $this->resetMembers();
        $group = StudentGroups::findOrFail($id);
        $this->manageGroupId = $group->id;
        $this->manageGroupTitle = $group->name;
        
        $this->availableStudents = Students::where('class_id', $group->class_id)
            ->where('status', true)
            ->get()
            ->toArray();
            
        $this->selectedStudents = $group->students()->pluck('students.id')->map(fn($id) => (string) $id)->toArray();
        
        $this->dispatch('open-modal', id: 'manage-members-modal');
    }

    public function saveMembers()
    {
        $group = StudentGroups::findOrFail($this->manageGroupId);
        
        // Prepare pivot data to include timestamps since we are using sync
        $pivotData = array_fill_keys($this->selectedStudents, ['created_at' => now(), 'updated_at' => now()]);
        
        $group->students()->sync($pivotData);

        $this->dispatch('close-modal', id: 'manage-members-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Anggota kelompok berhasil diperbarui.');
        $this->resetMembers();
    }

    public function confirmDelete($id)
    {
        $this->groupId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        $group = StudentGroups::findOrFail($this->groupId);

        // Cek relasi
        if ($group->members()->count() > 0 || $group->schedules()->count() > 0 || $group->productions()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Kelompok tidak bisa dihapus karena masih digunakan dalam jadwal, produksi, atau sudah memiliki anggota.');
            $this->dispatch('close-modal', id: 'delete-modal');
            return;
        }

        $group->delete();
        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Kelompok berhasil dihapus.');
    }

    public function render()
    {
        $groups = StudentGroups::query()
            ->with(['schoolClass'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('schoolClass', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.admin.student-groups.index', [
            'groups' => $groups,
            'classes' => Classes::where('status', true)->whereHas('students')->get(),
        ]);
    }
}
