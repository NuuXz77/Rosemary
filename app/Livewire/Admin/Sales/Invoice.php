<?php

namespace App\Livewire\Admin\Sales;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Invoice Penjualan')]
class Invoice extends Component
{
    public Sales $sale;
    public bool $isPinMode = false;

    public function mount(Sales $sale)
    {
        $this->sale->load(['items.product', 'cashier', 'shift', 'customer']);
        $this->isPinMode = session()->has('pos_student_id');
    }

    public function backToPOS()
    {
        $this->redirect(route('kasir.pos'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.sales.invoice', [
            'appName' => AppSetting::get('app_name', config('app.name', 'RoseMarry')),
        ]);
    }
}
