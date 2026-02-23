<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/**
 * AUTHENTICATION ROUTES
 *
 * Routes tanpa middleware 'auth': halaman login accessible oleh guest users
 */
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});

/**
 * DASHBOARD & PROTECTED ROUTES
 *
 * Routes dengan middleware 'auth': hanya accessible oleh authenticated users
 * Setiap route memiliki permission check di Livewire component atau blade view
 */
Route::middleware('auth')->group(function () {
    // Dashboard (main page saat login)
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard\Index::class)->name('dashboard.index');
    Route::get('/profile', \App\Livewire\Admin\Profile\Index::class)->name('profile.index');

    // ============= USER MANAGEMENT =============
    Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('roles.index');
    Route::get('/permissions', \App\Livewire\Admin\Permissions\Index::class)->name('permissions.index');
    Route::get('/category-permissions', \App\Livewire\Admin\CategoryPermissions\Index::class)->name('category-permissions.index');

    // ============= STUDENT MANAGEMENT (Livewire class belum dibuat) =============
    Route::get('/students', \App\Livewire\Admin\Students\Index::class)->name('students.index');
    Route::get('/student-groups', \App\Livewire\Admin\StudentGroups\Index::class)->name('student-groups.index');
    Route::get('/student-group-members', \App\Livewire\Admin\StudentGroupMembers\Index::class)->name('student-group-members.index');

    // ============= SCHEDULING (Livewire class belum dibuat) =============
    Route::get('/schedules', \App\Livewire\Admin\Schedules\Index::class)->name('schedules.index');

    // ============= INVENTORY MANAGEMENT =============
    Route::get('/materials', \App\Livewire\Admin\Materials\Index::class)->name('materials.index');                   // ✅ class ada
    Route::get('/material-stocks', \App\Livewire\Admin\MaterialStocks\Index::class)->name('material-stocks.index');    // ✅ class ada
    Route::get('/material-stock-logs', \App\Livewire\Admin\MaterialStockLogs\Index::class)->name('material-stock-logs.index'); // ✅ class ada
    Route::get('/products', \App\Livewire\Admin\Products\Index::class)->name('products.index');                     // ✅ class ada
    Route::get('/product-stocks', \App\Livewire\Admin\ProductStocks\Index::class)->name('product-stocks.index');       // ✅ class ada
    Route::get('/product-stock-logs', \App\Livewire\Admin\ProductStockLogs\Index::class)->name('product-stock-logs.index');   // ✅ class ada
    Route::get('/product-materials', \App\Livewire\Admin\ProductMaterials\Index::class)->name('product-materials.index');     // ✅ class ada

    // ============= TRANSACTION MANAGEMENT =============
    Route::get('/purchases', \App\Livewire\Admin\Purchases\Index::class)->name('purchases.index');                     // ✅ class ada
    Route::get('/productions', \App\Livewire\Admin\Productions\Index::class)->name('productions.index');            // ✅ class ada
    Route::get('/sales', \App\Livewire\Admin\Sales\Index::class)->name('sales.index');                                 // ✅ class ada
    Route::get('/sales/pos', \App\Livewire\Admin\Sales\POS::class)->name('sales.pos');                                   // ✅ NEW POS


    // ============= REPORTS & ANALYTICS =============
    Route::get('/reports/sales', \App\Livewire\Admin\Reports\Sales\Index::class)->name('reports.sales.index');          // class belum ada
    Route::get('/reports/purchases', \App\Livewire\Admin\Reports\Purchases\Index::class)->name('reports.purchases.index'); // class belum ada
    Route::get('/reports/productions', \App\Livewire\Admin\Reports\Productions\Index::class)->name('reports.productions.index'); // class belum ada
    Route::get('/reports/stocks', \App\Livewire\Admin\Reports\Stocks\Index::class)->name('reports.stocks.index');      // ✅ class ada
    Route::get('/reports/stocks/export', [\App\Http\Controllers\ExportController::class, 'exportStocks'])->name('reports.stocks.export');
    Route::get('/reports/schedules', \App\Livewire\Admin\Reports\Schedules\Index::class)->name('reports.schedules.index'); // class belum ada

    // ============= MASTER DATA =============
    Route::get('/master/categories', \App\Livewire\Admin\Master\Categories\Index::class)->name('master.categories.index'); // class belum ada
    Route::get('/master/units', \App\Livewire\Admin\Master\Units\Index::class)->name('master.units.index');                // class belum ada
    Route::get('/master/suppliers', \App\Livewire\Admin\Master\Suppliers\Index::class)->name('master.suppliers.index');      // ✅ class ada
    Route::get('/master/customers', \App\Livewire\Admin\Master\Customers\Index::class)->name('master.customers.index');   // class belum ada
    Route::get('/master/shifts', \App\Livewire\Admin\Master\Shifts\Index::class)->name('master.shifts.index');            // class belum ada
    Route::get('/master/divisions', \App\Livewire\Admin\Master\Divisions\Index::class)->name('master.divisions.index');   // class belum ada
    Route::get('/master/classes', \App\Livewire\Admin\Master\Classes\Index::class)->name('master.classes.index');         // class belum ada

    // ============= SETTINGS (Livewire class belum dibuat) =============
    Route::get('/settings/app', \App\Livewire\Admin\Settings\App\Index::class)->name('settings.app.index');
});

