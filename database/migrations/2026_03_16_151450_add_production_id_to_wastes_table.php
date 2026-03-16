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
        Schema::table('product_wastes', function (Blueprint $table) {
            $table->unsignedBigInteger('production_id')->nullable()->after('product_id');
            $table->foreign('production_id')->references('id')->on('productions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_wastes', function (Blueprint $table) {
            $table->dropForeign(['production_id']);
            $table->dropColumn('production_id');
        });
    }
};
