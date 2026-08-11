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
        Schema::table('locations', function (Blueprint $table) {
            $table->string('operating_hours')->nullable()->after('special_instructions');
            $table->integer('bin_size_gallons')->nullable()->after('operating_hours');
            $table->string('material_type')->default('used_cooking_oil')->nullable()->after('bin_size_gallons');
            $table->json('attachments')->nullable()->after('material_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn([
                'operating_hours',
                'bin_size_gallons',
                'material_type',
                'attachments',
            ]);
        });
    }
};
