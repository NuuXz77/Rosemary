<?php

namespace App\Livewire\Admin\Reports\Productions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Report Productions')]
    public function render()
    {
        return view('livewire.admin.reports.productions.index');
    }
}
