<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pharmacy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // stock_in, stock_out, adjustment, damaged, expired
            $table->integer('quantity');
            $table->string('direction'); // in, out
            $table->text('remarks')->nullable();
            $table->string('reference_number')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('pharmacy_id');
            $table->index('medicine_id');
            $table->index('type');
            $table->index('created_at');
        });

        Schema::create('inventory_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('medicine_batches')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transaction_lines');
        Schema::dropIfExists('inventory_transactions');
    }
};
