<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Admin Dashboard')]
    public function render()
    {
        return view('livewire.admin.dashboard.index');
    }
}
