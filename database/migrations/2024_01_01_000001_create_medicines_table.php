<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->string('generic_name');
            $table->string('brand_name')->nullable();
            $table->string('category');
            $table->string('dosage_strength')->nullable();
            $table->string('dosage_form');
            $table->string('unit');
            $table->text('description')->nullable();
            $table->integer('lead_time_days')->default(7);
            $table->integer('safety_stock')->default(10);
            $table->decimal('initial_average_daily_demand', 10, 2)->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            $table->index('pharmacy_id');
            $table->index('category');
            $table->index('is_archived');
            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
