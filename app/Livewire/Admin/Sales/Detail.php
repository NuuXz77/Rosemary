<?php

namespace App\Livewire\Admin\Sales;

use App\Models\AppSetting;
use App\Models\Sales;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Detail Penjualan')]
class Detail extends Component
{
    public Sales $sale;

    public function mount(Sales $sale)
    {
        $this->sale->load(['items.product', 'cashier', 'shift', 'customer']);
    }

    public function render()
    {
        return view('livewire.admin.sales.detail', [
            'appName' => AppSetting::get('app_name', config('app.name', 'RoseMarry')),
        ]);
    }
}
