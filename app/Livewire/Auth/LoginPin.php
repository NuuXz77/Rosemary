<?php

namespace App\Livewire\Auth;

use App\Models\Students;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.guest')]
#[Title('Kasir — Login PIN')]
class LoginPin extends Component
{
    /** @var array<int, string> */
    public array $digits = [];

    public ?string $studentName = null;

    public function mount(): void
    {
        // If already PIN-authenticated, skip the login screen
        if (session()->has('pos_student_id')) {
            $this->redirect(route('kasir.pos'), navigate: true);
        }
    }

    public function addDigit(string $digit): void
    {
        if (count($this->digits) >= 4) {
            return;
        }

        $this->digits[] = $digit;

        if (count($this->digits) === 4) {
            $this->verifyPin();
        }
    }

    public function removeDigit(): void
    {
        if (count($this->digits) > 0) {
            array_pop($this->digits);
        }
        $this->studentName = null;
    }

    public function clearPin(): void
    {
        $this->digits = [];
        $this->studentName = null;
    }

    private function verifyPin(): void
    {
        $pin = implode('', $this->digits);

        $student = Students::where('pin', $pin)
            ->where('status', true)
            ->first();

        if ($student) {
            // Store student identity in session for POS access
            session([
                'pos_student_id'   => $student->id,
                'pos_student_name' => $student->name,
            ]);

            $this->studentName = $student->name;

            $this->dispatch('show-toast', type: 'success', message: "Selamat datang, {$student->name}! Mengalihkan...");

            // Small delay so user can see the toast before redirect
            $this->js("setTimeout(() => { Livewire.navigate('/kasir/pos') }, 1200)");
        } else {
            $this->dispatch('show-toast', type: 'error', message: 'PIN salah atau akun tidak aktif!');
            $this->digits = [];
            $this->studentName = null;
        }
    }

    public function render()
    {
        return view('livewire.auth.login-pin');
    }
}
