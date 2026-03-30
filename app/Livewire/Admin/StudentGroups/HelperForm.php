<?php

namespace App\Livewire\Admin\StudentGroups;

use App\Models\Classes;
use App\Models\StudentGroups;
use App\Models\Students;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class HelperForm extends Component
{
    public bool $isOpen = false;

    // Group Information
    public string $name = '';
    public string $start_date = '';
    public string $end_date = '';
    public $class_id = '';
    public $division_id = null; // Opsional jika dibutuhkan

    // Member Configs
    public int $numOfMembers = 6;
    public string $filterStart = '';
    public string $filterEnd = '';
    public string $filterError = '';

    // Data for dropdowns
    public array $availableStudents = [];

    // Array to hold selected student IDs (length depends on $numOfMembers)
    public array $selectedMembers = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'class_id' => 'required|exists:classes,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'numOfMembers' => 'required|integer|min:1',
        'selectedMembers.*' => 'nullable|exists:students,id',
    ];

    public function mount()
    {
        $this->start_date = now()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
        $this->syncMemberArray();
    }

    public function updatedClassId()
    {
        $this->loadAvailableStudents();
    }

    public function updatedNumOfMembers()
    {
        // Paksa agar minimal selalu 1 (agar UI tidak rusak bila diisi 0 / minus)
        if ($this->numOfMembers < 1) {
            $this->numOfMembers = 1;
        }
        $this->syncMemberArray();
    }

    public function updatedFilterStart()
    {
        $this->loadAvailableStudents();
    }

    public function updatedFilterEnd()
    {
        $this->loadAvailableStudents();
    }

    public function syncMemberArray()
    {
        $currentSize = count($this->selectedMembers);

        if ($this->numOfMembers > $currentSize) {
            // Add new null elements if size increased
            for ($i = $currentSize; $i < $this->numOfMembers; $i++) {
                $this->selectedMembers[$i] = '';
            }
        } elseif ($this->numOfMembers < $currentSize) {
            // Remove elements if size decreased
            $this->selectedMembers = array_slice($this->selectedMembers, 0, $this->numOfMembers);
        }
    }

    public function loadAvailableStudents()
    {
        if (!$this->class_id) {
            $this->availableStudents = [];
            return;
        }

        // Ambil siswa di kelas ini yang statusnya aktif dan belum ada di kelompok mana pun
        $query = Students::where('class_id', $this->class_id)
            ->where('status', true)
            // Hanya ambil yang belum punya kelompok
            ->whereDoesntHave('groupMembers')
            ->orderBy('name', 'asc'); // Urut alfabet sbg simulasi absen

        $students = $query->get();

        $this->filterError = '';

        // Filter range "absen" berdasarkan urutan setelah di-get
        if ($this->filterStart !== '' || $this->filterEnd !== '') {
            $startIndex = (int) $this->filterStart > 0 ? ((int) $this->filterStart) - 1 : 0;
            $endIndex = (int) $this->filterEnd > 0 ? ((int) $this->filterEnd) - 1 : $students->count() - 1;

            // Safety check for indices
            if ($endIndex >= $startIndex) {
                // array_slice untuk koleksi laravel -> dipotong dan key-nya reset kembali mulai 0
                $students = collect(
                    $students
                        ->slice($startIndex, $endIndex - $startIndex + 1)
                        ->values()
                        ->all(),
                );
            } else {
                // Konfigurasi absen salah (Misal: dari absen 5 ke absen 2), kosongkan hasil
                $students = collect([]);
                $this->filterError = 'Absen tujuan tidak boleh kurang dari absen awal.';
            }
        }

        $this->availableStudents = $students->toArray();
    }

    public function randomizeMembers()
    {
        if (empty($this->availableStudents)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data siswa untuk diacak.');
            return;
        }

        // Coba acak siswa yang tersedia
        // Dan pastikan tidak duplicate terpilih dalam percobaan random ini

        $pool = $this->availableStudents;
        shuffle($pool);

        // Assign to available slots
        for ($i = 0; $i < $this->numOfMembers; $i++) {
            if (isset($pool[$i])) {
                $this->selectedMembers[$i] = (string) $pool[$i]['id'];
            } else {
                $this->selectedMembers[$i] = ''; // Kosongkan jika kurangan siswa
            }
        }
    }

    #[On('toggle-helper-form')]
    public function toggleForm()
    {
        // reset
        $this->reset(['name', 'class_id', 'filterStart', 'filterEnd', 'filterError']);
        $this->numOfMembers = 6;
        $this->start_date = now()->toDateString();
        $this->end_date = now()->endOfMonth()->toDateString();
        $this->syncMemberArray();
        $this->availableStudents = [];

        $this->dispatch('open-modal', id: 'helper-form-modal');
    }

    public function cancel()
    {
        $this->dispatch('close-create-modal');
    }

    public function save()
    {
        $this->validate();

        // Validasi Unique Nama d dalam satu Kelas (Mencegah Kelompok 1 dobel di kelas yg sama)
        $existsName = StudentGroups::where('class_id', $this->class_id)->where('name', $this->name)->exists();

        if ($existsName) {
            $this->addError('name', 'Nama kelompok sudah digunakan di kelas ini.');
            return;
        }

        // Validasi: Pastikan list ID yang dipilih tidak double
        $selectedIds = array_filter($this->selectedMembers, fn($val) => $val !== '');

        if (count($selectedIds) === 0) {
            $this->dispatch('show-toast', type: 'error', message: 'Pilih minimal satu anggota untuk kelompok ini.');
            return;
        }

        // Cek duplicate selections (ex: milih Budi 2 kali)
        if (count($selectedIds) !== count(array_unique($selectedIds))) {
            $this->dispatch('show-toast', type: 'error', message: 'Terdapat duplikasi anggota pada form. Silakan periksa kembali.');
            return;
        }

        // Cek kembali ketersediaan mereka di DB sebelum di insert
        // Untuk pastikan 1 siswa = 1 kelompok Strictness
        $alreadyInGroup = DB::table('student_group_members')->whereIn('student_id', $selectedIds)->exists();

        if ($alreadyInGroup) {
            $this->dispatch('show-toast', type: 'error', message: 'Beberapa siswa terpilih sudah berada di dalam kelompok lain.');
            return;
        }

        DB::beginTransaction();

        try {
            // Create the new group
            $group = StudentGroups::create([
                'name' => $this->name,
                'class_id' => $this->class_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => true,
            ]);

            // Add members
            $pivotData = [];
            $now = now();
            foreach ($selectedIds as $studentId) {
                $pivotData[] = [
                    'student_group_id' => $group->id,
                    'student_id' => $studentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('student_group_members')->insert($pivotData);

            DB::commit();

            $this->dispatch('show-toast', type: 'success', message: 'Kelompok dan anggota berhasil dibuat.');
            $this->dispatch('close-create-modal');
            $this->dispatch('groups-updated'); // trigger index component to refresh data
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Gagal membuat kelompok: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.student-groups.helper-form', [
            'classes' => Classes::where('status', true)->get(),
        ]);
    }
}
