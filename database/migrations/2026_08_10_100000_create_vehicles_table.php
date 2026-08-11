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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license_plate')->nullable();
            $table->string('make_model')->nullable();
            $table->decimal('capacity_gallons', 10, 2)->nullable();
            $table->decimal('capacity_lbs', 10, 2)->nullable();
            $table->string('status')->default('active');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
