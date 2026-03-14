<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sales;
use App\Models\Shift;
use App\Models\Students;
use App\Models\Classes;
use Carbon\Carbon;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada Class
        $class = Classes::first();
        if (!$class) {
            $class = Classes::create(['name' => '12 RPL 1', 'status' => true]);
        }

        // Pastikan ada Student
        $student = Students::first();
        if (!$student) {
            $student = Students::create([
                'pin' => '123456',
                'name' => 'Siswa Dummy',
                'class_id' => $class->id,
                'status' => true,
            ]);
        }

        // Pastikan ada Shift
        $shift = Shift::first();
        if (!$shift) {
            $shift = Shift::create([
                'name' => 'Shift Pagi',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'status' => true,
            ]);
        }

        // Insert Dummy Sales
        $items = [];
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::today()->subDays(rand(0, 14))->setHour(rand(8, 20))->setMinute(rand(0, 59));
            $amount = rand(5, 50) * 10000;

            $items[] = [
                'invoice_number' => 'INV-TEST-' . date('Ymd', $date->timestamp) . '-' . rand(100, 999),
                'shift_id' => $shift->id,
                'cashier_student_id' => $student->id,
                'subtotal' => $amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $amount,
                'paid_amount' => $amount,
                'change_amount' => 0,
                'payment_method' => ['cash', 'qris', 'transfer'][rand(0, 2)],
                'status' => 'paid',
                'created_at' => $date,
                'updated_at' => $date
            ];
        }

        Sales::insert($items);
    }
}
