<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'production_status')) {
                $table->enum('production_status', ['pending', 'cooking', 'done'])
                    ->default('pending')
                    ->after('status_order');
                $table->index('production_status');
            }

            if (!Schema::hasColumn('sales', 'called_at')) {
                $table->timestamp('called_at')->nullable()->after('production_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'called_at')) {
                $table->dropColumn('called_at');
            }

            if (Schema::hasColumn('sales', 'production_status')) {
                $table->dropIndex(['production_status']);
                $table->dropColumn('production_status');
            }
        });
    }
};
