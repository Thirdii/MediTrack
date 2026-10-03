<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiration_date');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->date('date_received');
            $table->timestamps();

            $table->index('pharmacy_id');
            $table->index('medicine_id');
            $table->index('expiration_date');
            $table->index('batch_number');
            $table->unique(['medicine_id', 'batch_number'], 'medicine_batch_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_batches');
    }
};
