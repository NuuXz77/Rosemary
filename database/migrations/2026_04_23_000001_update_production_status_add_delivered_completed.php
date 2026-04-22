<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Extend production_status enum with 'delivered' and 'completed'
        DB::statement("ALTER TABLE sales MODIFY COLUMN production_status ENUM('pending','cooking','done','delivered','completed') NOT NULL DEFAULT 'pending'");

        // 2. Drop called_at column (no longer used)
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'called_at')) {
                $table->dropColumn('called_at');
            }
        });
    }

    public function down(): void
    {
        // Revert any 'delivered'/'completed' rows back to 'done' before shrinking enum
        DB::table('sales')->whereIn('production_status', ['delivered', 'completed'])->update(['production_status' => 'done']);

        DB::statement("ALTER TABLE sales MODIFY COLUMN production_status ENUM('pending','cooking','done') NOT NULL DEFAULT 'pending'");

        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'called_at')) {
                $table->timestamp('called_at')->nullable()->after('production_status');
            }
        });
    }
};
