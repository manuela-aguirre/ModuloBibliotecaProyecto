<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejemplares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->string('codigo_barras', 50)->unique();
            $table->string('signatura', 50)->nullable();
            $table->string('edicion', 50)->nullable();
            $table->unsignedSmallInteger('anio_edicion')->nullable();
            $table->string('tipo_material', 30)->default('Libro');
            $table->string('numero_factura', 50)->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->date('fecha_adquisicion')->nullable();
            // disponible | reservado | prestado | danado | baja
            $table->string('estado', 20)->default('disponible')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejemplares');
    }
};