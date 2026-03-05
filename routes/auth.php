<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ============================================
// GUEST ROUTES (Belum Login)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', App\Livewire\Auth\Login::class)->name('login');
});

// ============================================
// KASIR SISWA — Login PIN
// Autentikasi berbasis sesi (bukan Laravel Auth).
// Sidebar Spatie otomatis kosong karena siswa
// bukan user Laravel → @can selalu false.
// Layout sama dengan admin (app.blade.php).
// ============================================
Route::get('/login/pin', App\Livewire\Auth\LoginPin::class)->name('pos.login');

Route::middleware('pin.auth')->group(function () {
    Route::get('/kasir/pos', App\Livewire\Kasir\POS::class)->name('kasir.pos');
    Route::get('/kasir/checkout', App\Livewire\Admin\Sales\Checkout::class)->name('kasir.checkout');

    Route::get('/kasir/logout', function () {
        session()->forget(['pos_student_id', 'pos_student_name']);
        return redirect()->route('pos.login');
    })->name('kasir.logout');
});

// ============================================
// AUTHENTICATED ROUTES (Sudah Login)
//
// PENJELASAN MIDDLEWARE PERMISSION:
// - middleware('can:xxx') = User HARUS punya permission untuk akses route ini
// - Jika tidak punya, akan redirect ke 403 Forbidden
// - CRUD operations di-check lagi di dalam komponen Livewire
// ============================================
Route::middleware('auth')->group(function () {

    // --------------------------------------------
    // DASHBOARD & PROFILE
    // --------------------------------------------
    Route::get('/dashboard', App\Livewire\Admin\Dashboard\Index::class)
        ->name('dashboard.index')
        ->middleware('can:dashboard.view');

    Route::get('/profile', App\Livewire\Admin\Profile\Index::class)
        ->name('profile.index');

    // --------------------------------------------
    // MANAJEMEN PENGGUNA
    // --------------------------------------------
    Route::middleware('can:users.manage')->group(function () {
        Route::get('/users', App\Livewire\Admin\Users\Index::class)->name('users.index');
    });

    Route::middleware('can:roles.view')->group(function () {
        Route::get('/roles', App\Livewire\Admin\Roles\Index::class)->name('roles.index');
    });

    Route::middleware('can:permissions.view')->group(function () {
        Route::get('/permissions', App\Livewire\Admin\Permissions\Index::class)->name('permissions.index');
    });

    Route::middleware('can:permissions.manage')->group(function () {
        Route::get('/category-permissions', App\Livewire\Admin\CategoryPermissions\Index::class)->name('category-permissions.index');
    });

    // --------------------------------------------
    // MANAJEMEN SISWA
    // --------------------------------------------
    Route::middleware('can:students.view')->group(function () {
        Route::get('/students', App\Livewire\Admin\Students\Index::class)->name('students.index');
    });

    Route::middleware('can:student-groups.view')->group(function () {
        Route::get('/student-groups', App\Livewire\Admin\StudentGroups\Index::class)->name('student-groups.index');
    });

    Route::middleware('can:student-group-members.view')->group(function () {
        Route::get('/student-group-members', App\Livewire\Admin\StudentGroupMembers\Index::class)->name('student-group-members.index');
    });

    // --------------------------------------------
    // PENJADWALAN
    // --------------------------------------------
    Route::middleware('can:schedules.view')->group(function () {
        Route::get('/schedules', App\Livewire\Admin\Schedules\Index::class)->name('schedules.index');
    });

    // --------------------------------------------
    // MANAJEMEN INVENTARIS
    // --------------------------------------------
    Route::middleware('can:materials.view')->group(function () {
        Route::get('/materials', App\Livewire\Admin\Materials\Index::class)->name('materials.index');
    });

    Route::middleware('can:material-stocks.view')->group(function () {
        Route::get('/material-stocks', App\Livewire\Admin\MaterialStocks\Index::class)->name('material-stocks.index');
    });

    Route::middleware('can:material-stock-logs.view')->group(function () {
        Route::get('/material-stock-logs', App\Livewire\Admin\MaterialStockLogs\Index::class)->name('material-stock-logs.index');
    });

    Route::middleware('can:products.view')->group(function () {
        Route::get('/products', App\Livewire\Admin\Products\Index::class)->name('products.index');
    });

    Route::middleware('can:product-stocks.view')->group(function () {
        Route::get('/product-stocks', App\Livewire\Admin\ProductStocks\Index::class)->name('product-stocks.index');
    });

    Route::middleware('can:product-stock-logs.view')->group(function () {
        Route::get('/product-stock-logs', App\Livewire\Admin\ProductStockLogs\Index::class)->name('product-stock-logs.index');
    });

    Route::middleware('can:product-materials.view')->group(function () {
        Route::get('/product-materials', App\Livewire\Admin\ProductMaterials\Index::class)->name('product-materials.index');
    });

    // --------------------------------------------
    // MANAJEMEN TRANSAKSI
    // --------------------------------------------
    Route::middleware('can:purchases.view')->group(function () {
        Route::get('/purchases', App\Livewire\Admin\Purchases\Index::class)->name('purchases.index');
    });

    Route::middleware('can:productions.view')->group(function () {
        Route::get('/productions', App\Livewire\Admin\Productions\Index::class)->name('productions.index');
    });

    Route::middleware('can:sales.view')->group(function () {
        Route::get('/sales', App\Livewire\Admin\Sales\Index::class)->name('sales.index');
        Route::get('/sales/pos', App\Livewire\Admin\Sales\POS::class)->name('sales.pos');
        Route::get('/sales/checkout', App\Livewire\Admin\Sales\Checkout::class)->name('sales.checkout');
    });

    // --------------------------------------------
    // LAPORAN & ANALITIK
    // --------------------------------------------
    Route::middleware('can:reports.sales.view')->group(function () {
        Route::get('/reports/sales', App\Livewire\Admin\Reports\Sales\Index::class)->name('reports.sales.index');
    });

    Route::middleware('can:reports.purchases.view')->group(function () {
        Route::get('/reports/purchases', App\Livewire\Admin\Reports\Purchases\Index::class)->name('reports.purchases.index');
    });

    Route::middleware('can:reports.productions.view')->group(function () {
        Route::get('/reports/productions', App\Livewire\Admin\Reports\Productions\Index::class)->name('reports.productions.index');
    });

    Route::middleware('can:reports.stocks.view')->group(function () {
        Route::get('/reports/stocks', App\Livewire\Admin\Reports\Stocks\Index::class)->name('reports.stocks.index');
        Route::get('/reports/stocks/export', [App\Http\Controllers\ExportController::class, 'exportStocks'])->name('reports.stocks.export');
    });

    Route::middleware('can:reports.schedules.view')->group(function () {
        Route::get('/reports/schedules', App\Livewire\Admin\Reports\Schedules\Index::class)->name('reports.schedules.index');
    });

    // --------------------------------------------
    // MASTER DATA
    // --------------------------------------------
    Route::middleware('can:master.categories.view')->group(function () {
        Route::get('/master/categories', App\Livewire\Admin\Master\Categories\Index::class)->name('master.categories.index');
    });

    Route::middleware('can:master.units.view')->group(function () {
        Route::get('/master/units', App\Livewire\Admin\Master\Units\Index::class)->name('master.units.index');
    });

    Route::middleware('can:master.suppliers.view')->group(function () {
        Route::get('/master/suppliers', App\Livewire\Admin\Master\Suppliers\Index::class)->name('master.suppliers.index');
    });

    Route::middleware('can:master.customers.view')->group(function () {
        Route::get('/master/customers', App\Livewire\Admin\Master\Customers\Index::class)->name('master.customers.index');
    });

    Route::middleware('can:master.shifts.view')->group(function () {
        Route::get('/master/shifts', App\Livewire\Admin\Master\Shifts\Index::class)->name('master.shifts.index');
    });

    Route::middleware('can:master.divisions.view')->group(function () {
        Route::get('/master/divisions', App\Livewire\Admin\Master\Divisions\Index::class)->name('master.divisions.index');
    });

    Route::middleware('can:master.classes.view')->group(function () {
        Route::get('/master/classes', App\Livewire\Admin\Master\Classes\Index::class)->name('master.classes.index');
    });

    // --------------------------------------------
    // PENGATURAN
    // --------------------------------------------
    Route::middleware('can:settings.app.view')->group(function () {
        Route::get('/settings/app', App\Livewire\Admin\Settings\App\Index::class)->name('settings.app.index');
    });

    // --------------------------------------------
    // LOGOUT
    // --------------------------------------------
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

});

