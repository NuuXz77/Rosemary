<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'label' => 'Nama Aplikasi',
                'value' => 'Rosemary App',
                'group' => 'general',
                'type' => 'text',
                'description' => 'Nama utama dari aplikasi ini.'
            ],
            [
                'key' => 'app_description',
                'label' => 'Deskripsi Aplikasi',
                'value' => 'Sistem Informasi Manajemen Sekolah dan Inventaris.',
                'group' => 'general',
                'type' => 'textarea',
                'description' => 'Deskripsi singkat aplikasi.'
            ],
            [
                'key' => 'contact_email',
                'label' => 'Email Kontak',
                'value' => 'admin@rosemary.com',
                'group' => 'contact',
                'type' => 'email',
                'description' => 'Email resmi untuk dukungan teknis.'
            ],
            [
                'key' => 'registration_enabled',
                'label' => 'Pendaftaran Dibuka',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Mengontrol apakah pendaftaran user baru diizinkan.'
            ],
            [
                'key' => 'cashier_schedule_mode',
                'label' => 'Mode Jadwal Kasir',
                'value' => 'flexible',
                'group' => 'system',
                'type' => 'text',
                'description' => 'Mode validasi login PIN kasir. Isi: strict (wajib jadwal) atau flexible (jadwal opsional).'
            ],
            [
                'key' => 'sound_notifications_enabled',
                'label' => 'Aktifkan Notifikasi Suara',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Mengaktifkan notifikasi suara secara global.'
            ],
            [
                'key' => 'sound_notifications_cashier',
                'label' => 'Notifikasi Suara Halaman Kasir',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi suara untuk role kasir.'
            ],
            [
                'key' => 'sound_notifications_production',
                'label' => 'Notifikasi Suara Halaman Production',
                'value' => '1',
                'group' => 'system',
                'type' => 'boolean',
                'description' => 'Aktifkan notifikasi suara untuk role production.'
            ],
            [
                'key' => 'sound_notification_volume',
                'label' => 'Volume Notifikasi Suara',
                'value' => '80',
                'group' => 'system',
                'type' => 'number',
                'description' => 'Volume notifikasi suara (0-100).'
            ],
            [
                'key' => 'sound_notification_message_template',
                'label' => 'Template Pesan Notifikasi Suara',
                'value' => 'Pesanan baru masuk. Silakan cek antrian.',
                'group' => 'system',
                'type' => 'textarea',
                'description' => 'Template teks untuk notifikasi suara otomatis.'
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\AppSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
