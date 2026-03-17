<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('material_wastes', function (Blueprint $table) {
            $table->foreignId('production_id')->nullable()->after('material_id')->constrained('productions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_wastes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('production_id');
        });
    }
};
