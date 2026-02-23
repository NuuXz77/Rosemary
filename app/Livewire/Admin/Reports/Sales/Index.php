<?php

namespace App\Livewire\Admin\Reports\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Report Sales')]
    public function render()
    {
        return view('livewire.admin.reports.sales.index');
    }
}
