<?php

namespace App\Livewire\Auth;

use App\Models\Schedules;
use App\Models\StudentAttendance;
use App\Models\Students;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        if (!$student) {
            $this->dispatch('show-toast', type: 'error', message: 'PIN salah atau akun tidak aktif!');
            $this->digits = [];
            $this->studentName = null;
            return;
        }

        // Check if student has cashier schedule for today
        $today = now()->toDateString();
        $schedule = Schedules::with('shift')
            ->where('type', 'cashier')
            ->where('student_id', $student->id)
            ->whereDate('date', $today)
            ->where('status', true)
            ->first();

        if (!$schedule) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Kamu tidak memiliki jadwal kasir hari ini. Silakan cek jadwal kamu!');
            $this->digits = [];
            $this->studentName = null;
            return;
        }

        // Check if already logged in today for this schedule
        $existingAttendance = StudentAttendance::where('student_id', $student->id)
            ->where('schedule_id', $schedule->id)
            ->first();

        if ($existingAttendance && $existingAttendance->login_time) {
            // Already logged in, just let them through
            $this->loginSuccess($student);
            return;
        }

        // Validate shift time and record attendance
        $currentTime = now();
        $shift = $schedule->shift;

        $shiftStart = now()->setTimeFromTimeString($shift->start_time->format('H:i:s'));
        $shiftEnd = now()->setTimeFromTimeString($shift->end_time->format('H:i:s'));
        $toleranceMinutes = $shift->tolerance ?? 15;
        $maxAllowedTime = $shiftStart->copy()->addMinutes($toleranceMinutes);

        // Past shift end - deny login
        if ($currentTime->isAfter($shiftEnd)) {
            $this->dispatch('show-toast',
                type: 'error',
                message: 'Shift sudah berakhir. Kamu tidak bisa login lagi!');
            $this->digits = [];
            $this->studentName = null;
            return;
        }

        // Determine attendance status
        $lateMinutes = 0;
        if ($currentTime->lte($shiftStart)) {
            $attendanceStatus = 'on_time';
        } elseif ($currentTime->lte($maxAllowedTime)) {
            $attendanceStatus = 'on_time';
        } else {
            $attendanceStatus = 'late';
            $lateMinutes = (int) $currentTime->diffInMinutes($shiftStart);
        }

        // Record attendance
        StudentAttendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'schedule_id' => $schedule->id,
            ],
            [
                'shift_id' => $shift->id,
                'date' => $today,
                'login_time' => $currentTime->format('H:i:s'),
                'shift_start' => $shift->start_time->format('H:i:s'),
                'status' => $attendanceStatus,
                'late_minutes' => $lateMinutes,
            ]
        );

        if ($attendanceStatus === 'late') {
            $this->dispatch('show-toast',
                type: 'warning',
                message: "Kamu terlambat {$lateMinutes} menit. Kehadiran tetap dicatat.");
        }

        $this->loginSuccess($student);
    }

    private function loginSuccess(Students $student): void
    {
        // Get shift_id from today's cashier schedule
        $schedule = Schedules::with('shift')
            ->where('type', 'cashier')
            ->where('student_id', $student->id)
            ->whereDate('date', now()->toDateString())
            ->where('status', true)
            ->first();

        session([
            'pos_student_id'   => $student->id,
            'pos_student_name' => $student->name,
            'pos_shift_id'     => $schedule?->shift_id,
        ]);

        // Login ke Laravel Auth menggunakan shared Cashier user
        // agar Spatie role/permission aktif untuk sidebar
        $cashierUser = User::whereHas('roles', fn($q) => $q->where('name', 'cashier'))
            ->where('is_active', true)
            ->first();
        if ($cashierUser) {
            Auth::login($cashierUser);
        }

        $this->studentName = $student->name;

        $this->dispatch('show-toast', type: 'success', message: "Selamat datang, {$student->name}! Mengalihkan...");

        $this->js("setTimeout(() => { Livewire.navigate('/kasir/pos') }, 1200)");
    }

    public function render()
    {
        return view('livewire.auth.login-pin');
    }
}
