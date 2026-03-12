<?php

namespace App\Livewire\Kasir;

use App\Livewire\Admin\Sales\POS as BasePOS;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

/**
 * Student-facing POS component.
 *
 * Extends the admin POS with:
 *  - A minimal layout (no admin sidebar, no Spatie auth guard)
 *  - Auto-injection of cashier_student_id from the PIN session
 *  - A PIN logout action
 *
 * Access is gated by the `pin.auth` middleware, which checks that
 * `session('pos_student_id')` has been set after a successful PIN login.
 */
#[Layout('components.layouts.app')]
#[Title('Kasir POS')]
class POS extends BasePOS
{
    /** Marks this as student PIN mode — hides admin-only selectors in the blade. */
    public bool $pinMode = true;

    public function mount(): void
    {
        // Run parent mount (auto-detects current shift, etc.)
        parent::mount();

        // Override: always set cashier from the authenticated PIN session
        if ($studentId = session('pos_student_id')) {
            $this->cashier_student_id = $studentId;
        }

        // Fallback: use shift from PIN login session if auto-detect failed
        if (!$this->shift_id && session('pos_shift_id')) {
            $this->shift_id = session('pos_shift_id');
        }
    }

    /**
     * Clear the PIN session and redirect back to the PIN login screen.
     */
    public function pinLogout(): void
    {
        session()->forget(['pos_student_id', 'pos_student_name', 'pos_shift_id']);

        $this->redirect(route('pos.login'), navigate: true);
    }
}
