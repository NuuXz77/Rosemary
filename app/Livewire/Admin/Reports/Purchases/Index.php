<?php

namespace App\Livewire\Admin\Reports\Purchases;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Report Purchases')]
    public function render()
    {
        return view('livewire.admin.reports.purchases.index');
    }
}
