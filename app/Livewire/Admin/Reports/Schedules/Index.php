<?php

namespace App\Livewire\Admin\Reports\Schedules;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Report Schedules')]
    public function render()
    {
        return view('livewire.admin.reports.schedules.index');
    }
}
