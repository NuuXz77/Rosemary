<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;

// Route untuk homepage - redirect ke login
Route::get('/', function () {
    // Student PIN session takes priority
    if (session()->has('pos_student_id')) {
        return redirect()->route('kasir.pos');
    }

    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('Production')) {
            return redirect()->route('productions.index');
        } elseif ($user->hasRole('Inventory')) {
            return redirect()->route('material-stocks.index');
        } elseif ($user->hasRole('Cashier')) {
            return redirect()->route('sales.pos');
        }
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
});

require __DIR__ . '/auth.php';
