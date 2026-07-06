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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('service_address');
            $table->text('map_link')->nullable();
            $table->text('special_instructions')->nullable();
            $table->enum('service_frequency', ['weekly', 'biweekly', 'monthly', 'on_call'])->default('weekly');
            $table->decimal('reimbursement_rate', 10, 2)->default(0.00);
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->foreignId('default_route_id')->nullable()->constrained('routes')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
