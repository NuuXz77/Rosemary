<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_wastes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->decimal('qty', 15, 2);
            $table->string('reason');
            $table->date('waste_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_wastes');
    }
};
