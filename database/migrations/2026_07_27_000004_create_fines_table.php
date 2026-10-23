<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->constrained('borrowings')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('days_late');
            $table->decimal('daily_rate', 12, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique('borrowing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
