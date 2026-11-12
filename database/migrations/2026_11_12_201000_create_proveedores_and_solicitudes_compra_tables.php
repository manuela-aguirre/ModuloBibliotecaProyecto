<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('contacto', 120)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('solicitudes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('libro_id')->constrained('libros')->restrictOnDelete();
            $table->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->foreignId('solicitado_por')->constrained('users')->restrictOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('recibido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->string('coleccion', 20)->default('general');
            $table->string('estado', 20)->default('solicitada')->index();
            $table->text('observaciones')->nullable();
            $table->timestamp('aprobada_at')->nullable();
            $table->timestamp('recibida_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_compra');
        Schema::dropIfExists('proveedores');
    }
};
