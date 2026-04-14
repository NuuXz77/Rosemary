<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'queue_number')) {
                $table->string('queue_number', 20)->nullable()->after('invoice_number');
                $table->index('queue_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'queue_number')) {
                $table->dropIndex(['queue_number']);
                $table->dropColumn('queue_number');
            }
        });
    }
};
