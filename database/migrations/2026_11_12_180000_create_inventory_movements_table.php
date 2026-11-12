<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros')->restrictOnDelete();
            $table->foreignId('ejemplar_id')->nullable()->constrained('ejemplares')->nullOnDelete();
            $table->string('ejemplar_codigo')->nullable();
            $table->string('type', 10);
            $table->string('concept', 40);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('balance');
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['libro_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
