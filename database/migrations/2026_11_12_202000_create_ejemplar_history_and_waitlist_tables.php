<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejemplar_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejemplar_id')->nullable()->constrained('ejemplares')->nullOnDelete();
            $table->foreignId('libro_id')->nullable()->constrained('libros')->nullOnDelete();
            $table->string('codigo_barras', 50)->index();
            $table->string('titulo_libro', 150);
            $table->string('tipo', 30)->index();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20)->nullable();
            $table->foreignId('ubicacion_anterior_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('ubicacion_nueva_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('referencia_type')->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('ocurrido_at')->index();
            $table->timestamps();

            $table->index(['ejemplar_id', 'ocurrido_at']);
        });

        Schema::create('lista_espera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete();
            $table->string('estado', 20)->default('espera')->index();
            $table->timestamp('atendida_at')->nullable();
            $table->timestamps();

            $table->index(['libro_id', 'estado', 'id']);
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_espera');
        Schema::dropIfExists('ejemplar_eventos');
    }
};
