<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('borrowed_at')->index();
            $table->date('due_date')->index();
            $table->dateTime('returned_at')->nullable()->index();
            $table->enum('status', ['borrowed', 'returned', 'overdue', 'cancelled'])->default('borrowed')->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['collection_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
