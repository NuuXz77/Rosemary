<?php

namespace App\Livewire\Admin\StudentGroups;

use App\Models\StudentGroups;
use App\Models\Classes;
use App\Models\Students;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
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

    #[On('groups-updated')]
    public function groupsUpdated()
    {
        $this->resetPage();
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

        // Validasi Unique Nama
        $existsName = StudentGroups::where('class_id', $this->class_id)
            ->where('name', $this->name)
            ->exists();
            
        if ($existsName) {
            $this->addError('name', 'Nama kelompok sudah digunakan di kelas ini.');
            return;
        }

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

        // Mencegah perubahan kelas jika kelompok sudah memiliki anggota (Bisa merusak data integrity)
        if ($group->class_id != $this->class_id && $group->members()->count() > 0) {
            $this->addError('class_id', 'Gagal: Tidak bisa mengubah kelas karena kelompok ini masih berisi siswa dari kelas sebelumnya.');
            return;
        }

        // Validasi Unique Nama saat edit
        $existsName = StudentGroups::where('class_id', $this->class_id)
            ->where('name', $this->name)
            ->where('id', '!=', $group->id)
            ->exists();
            
        if ($existsName) {
            $this->addError('name', 'Nama kelompok sudah digunakan di kelas ini.');
            return;
        }

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
            ->where(function ($query) use ($group) {
                // Tampilkan siswa yang belum punya kelompok ATAU yang memang sudah di kelompok ini
                $query->whereDoesntHave('groupMembers')
                      ->orWhereHas('groupMembers', function ($q) use ($group) {
                          $q->where('student_group_id', $group->id);
                      });
            })
            ->get()
            ->toArray();
            
        $this->selectedStudents = $group->students()->pluck('students.id')->map(fn($id) => (string) $id)->toArray();
        
        $this->dispatch('open-modal', id: 'manage-members-modal');
    }

    public function saveMembers()
    {
        $group = StudentGroups::findOrFail($this->manageGroupId);

        // Validasi Strict: Pastikan siswa yang dipilih tidak ada di kelompok lain (kecuali kelompok ini sendiri)
        $alreadyInOtherGroup = \Illuminate\Support\Facades\DB::table('student_group_members')
            ->whereIn('student_id', $this->selectedStudents)
            ->where('student_group_id', '!=', $group->id)
            ->exists();

        if ($alreadyInOtherGroup) {
            $this->dispatch('show-toast', type: 'error', message: 'Gagal! Beberapa siswa yang dicentang sudah berada di kelompok lain.');
            return;
        }
        
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

        // Cek relasi (Jadwal dan Produksi, tidak perlu blokir bila sekedar punya anggota karena otomatis cascade)
        if ($group->schedules()->count() > 0 || $group->productions()->count() > 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Kelompok tidak bisa dihapus karena masih digunakan dalam aktifitas jadwal atau produksi.');
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
