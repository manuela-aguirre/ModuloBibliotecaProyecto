<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_prestamo');
            $table->date('fecha_limite');
            $table->date('fecha_devolucion')->nullable();
            $table->decimal('multa', 10, 2)->default(0);
            $table->boolean('multa_pagada')->default(false);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('prestamo_ejemplar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->foreignId('ejemplar_id')->constrained('ejemplares')->restrictOnDelete();
            // prestado | devuelto | danado
            $table->string('estado', 20)->default('prestado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamo_ejemplar');
        Schema::dropIfExists('prestamos');
    }
};