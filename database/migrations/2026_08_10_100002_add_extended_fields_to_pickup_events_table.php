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
        Schema::table('pickup_events', function (Blueprint $table) {
            $table->string('material_type')->default('used_cooking_oil')->nullable()->after('status');
            $table->string('unit')->default('lbs')->nullable()->after('material_type');
            $table->decimal('gross_weight', 10, 2)->nullable()->after('unit');
            $table->decimal('tare_weight', 10, 2)->nullable()->after('gross_weight');
            $table->decimal('net_weight', 10, 2)->nullable()->after('tare_weight');
            $table->integer('container_count')->default(1)->nullable()->after('net_weight');
            $table->string('driver_signature_path')->nullable()->after('container_count');
            $table->string('customer_signature_path')->nullable()->after('driver_signature_path');
            $table->json('photos')->nullable()->after('customer_signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_events', function (Blueprint $table) {
            $table->dropColumn([
                'material_type',
                'unit',
                'gross_weight',
                'tare_weight',
                'net_weight',
                'container_count',
                'driver_signature_path',
                'customer_signature_path',
                'photos',
            ]);
        });
    }
};
