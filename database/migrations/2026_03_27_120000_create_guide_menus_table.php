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
        Schema::create('guide_menus', function (Blueprint $table) {
            $table->id();
            $table->string('role_key', 30);
            $table->string('module_key', 50)->nullable();
            $table->string('label', 100);
            $table->string('route_name', 100)->nullable();
            $table->string('external_url', 255)->nullable();
            $table->string('required_permission', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['role_key', 'is_active']);
            $table->index('module_key');
            $table->index('required_permission');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_menus');
    }
};
