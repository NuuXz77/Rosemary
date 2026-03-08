<?php

namespace App\Livewire\Admin\MaterialWastes;

use App\Models\Materials;
use App\Models\MaterialWastes;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Title('Laporan Limbah Bahan (Waste)')]

    public string $search = '';
    public int $perPage = 10;

    // Form
    public $material_id;
    public $qty;
    public $reason;
    public $waste_date;
    public $wasteId;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'material_id' => 'required|exists:materials,id',
            'qty' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'waste_date' => 'required|date',
        ];
    }

    public function mount()
    {
        $this->waste_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetFields()
    {
        $this->reset(['material_id', 'qty', 'reason', 'waste_date', 'wasteId', 'isEdit']);
        $this->waste_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetFields();
        $this->dispatch('open-modal', id: 'waste-modal');
    }

    public function store()
    {
        $this->validate();

        MaterialWastes::create([
            'material_id' => $this->material_id,
            'qty' => $this->qty,
            'reason' => $this->reason,
            'waste_date' => $this->waste_date,
            'created_by' => Auth::id(),
        ]);

        $this->dispatch('close-modal', id: 'waste-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Limbah bahan berhasil dicatat.');
        $this->resetFields();
    }

    public function confirmDelete($id)
    {
        $this->wasteId = $id;
        $this->dispatch('open-modal', id: 'delete-modal');
    }

    public function delete()
    {
        // Catatan: Jika waste dihapus, apakah stok harus dikembalikan? 
        // Dalam sistem inventaris yang ketat, biasanya data waste tidak dihapus, tapi di-cancel.
        // Untuk saat ini kita izinkan hapus tapi tidak mengembalikan stok untuk menjaga integritas historis.
        $waste = MaterialWastes::findOrFail($this->wasteId);
        $waste->delete();

        $this->dispatch('close-modal', id: 'delete-modal');
        $this->dispatch('show-toast', type: 'success', message: 'Data limbah berhasil dihapus.');
    }

    public function render()
    {
        $wastes = MaterialWastes::query()
            ->with(['material', 'creator'])
            ->whereHas('material', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orWhere('reason', 'like', '%' . $this->search . '%')
            ->orderBy('waste_date', 'desc')
            ->paginate($this->perPage);

        $materials = Materials::where('status', true)->get();

        return view('livewire.admin.material-wastes.index', [
            'wastes' => $wastes,
            'materials' => $materials,
        ]);
    }
}
