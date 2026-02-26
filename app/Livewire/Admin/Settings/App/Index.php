<?php

namespace App\Livewire\Admin\Settings\App;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
class Index extends Component
{
    #[Title('Settings App')]
    public function render()
    {
        return view('livewire.admin.settings.app.index');
    }
}
