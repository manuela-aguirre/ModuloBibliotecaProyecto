<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ejemplar_id')->constrained('ejemplares')->cascadeOnDelete();
            $table->date('fecha_retiro');                       // día en que el usuario pasará por el libro
            $table->string('estado', 20)->default('activa');    // activa | cancelada | caducada | retirada
            $table->timestamps();

            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};