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
    
    // ============= USER MANAGEMENT =============
    Route::get('/users', \App\Livewire\Admin\Users\Index::class)->name('users.index');
    Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)->name('roles.index');
    Route::get('/permissions', \App\Livewire\Admin\Permissions\Index::class)->name('permissions.index');
    Route::get('/category-permissions', \App\Livewire\Admin\CategoryPermissions\Index::class)->name('category-permissions.index');

    // ============= STUDENT MANAGEMENT (commented - views belum available) =============
    // Route::get('/students', \App\Livewire\Admin\Students\Index::class)->name('students.index');
    // Route::get('/student-groups', \App\Livewire\Admin\StudentGroups\Index::class)->name('student-groups.index');
    // Route::get('/student-group-members', \App\Livewire\Admin\StudentGroupMembers\Index::class)->name('student-group-members.index');

    // ============= SCHEDULING (commented - views belum available) =============
    // Route::get('/schedules', \App\Livewire\Admin\Schedules\Index::class)->name('schedules.index');

    // ============= INVENTORY MANAGEMENT (commented - views belum available) =============
    // Route::get('/materials', \App\Livewire\Admin\Materials\Index::class)->name('materials.index');
    // Route::get('/material-stocks', \App\Livewire\Admin\MaterialStocks\Index::class)->name('material-stocks.index');
    // Route::get('/material-stock-logs', \App\Livewire\Admin\MaterialStockLogs\Index::class)->name('material-stock-logs.index');
    // Route::get('/products', \App\Livewire\Admin\Products\Index::class)->name('products.index');
    // Route::get('/product-stocks', \App\Livewire\Admin\ProductStocks\Index::class)->name('product-stocks.index');
    // Route::get('/product-stock-logs', \App\Livewire\Admin\ProductStockLogs\Index::class)->name('product-stock-logs.index');
    // Route::get('/product-materials', \App\Livewire\Admin\ProductMaterials\Index::class)->name('product-materials.index');

    // ============= TRANSACTION MANAGEMENT (commented - views belum available) =============
    // Route::get('/purchases', \App\Livewire\Admin\Purchases\Index::class)->name('purchases.index');
    // Route::get('/productions', \App\Livewire\Admin\Productions\Index::class)->name('productions.index');
    // Route::get('/sales', \App\Livewire\Admin\Sales\Index::class)->name('sales.index');

    // ============= REPORTS & ANALYTICS (commented - views belum available) =============
    // Route::get('/reports/sales', \App\Livewire\Admin\Reports\Sales\Index::class)->name('reports.sales.index');
    // Route::get('/reports/purchases', \App\Livewire\Admin\Reports\Purchases\Index::class)->name('reports.purchases.index');
    // Route::get('/reports/productions', \App\Livewire\Admin\Reports\Productions\Index::class)->name('reports.productions.index');
    // Route::get('/reports/stocks', \App\Livewire\Admin\Reports\Stocks\Index::class)->name('reports.stocks.index');
    // Route::get('/reports/schedules', \App\Livewire\Admin\Reports\Schedules\Index::class)->name('reports.schedules.index');

    // ============= MASTER DATA (commented - views belum available) =============
    // Route::get('/master/categories', \App\Livewire\Admin\Master\Categories\Index::class)->name('master.categories.index');
    // Route::get('/master/units', \App\Livewire\Admin\Master\Units\Index::class)->name('master.units.index');
    // Route::get('/master/suppliers', \App\Livewire\Admin\Master\Suppliers\Index::class)->name('master.suppliers.index');
    // Route::get('/master/customers', \App\Livewire\Admin\Master\Customers\Index::class)->name('master.customers.index');
    // Route::get('/master/shifts', \App\Livewire\Admin\Master\Shifts\Index::class)->name('master.shifts.index');
    // Route::get('/master/divisions', \App\Livewire\Admin\Master\Divisions\Index::class)->name('master.divisions.index');
    // Route::get('/master/classes', \App\Livewire\Admin\Master\Classes\Index::class)->name('master.classes.index');

    // ============= SETTINGS (commented - views belum available) =============
    // Route::get('/settings/app', \App\Livewire\Admin\Settings\App\Index::class)->name('settings.app.index');
});

