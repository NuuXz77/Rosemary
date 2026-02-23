<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;

// Route untuk homepage - redirect ke login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';
