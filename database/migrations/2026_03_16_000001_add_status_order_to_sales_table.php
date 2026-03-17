<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sales', 'status_order')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->enum('status_order', ['Take away', 'Dine in'])
                    ->default('Take away')
                    ->after('table_number');
            });

            DB::table('sales')
                ->whereNotNull('table_number')
                ->where('table_number', '!=', '')
                ->update(['status_order' => 'Dine in']);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'status_order')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('status_order');
            });
        }
    }
};