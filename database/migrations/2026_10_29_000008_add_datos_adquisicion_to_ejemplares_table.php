<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->string('numero_entrada', 30)->nullable()->after('codigo_barras');
            $table->unsignedSmallInteger('paginas')->nullable()->after('anio_edicion');
            $table->string('medio_adquisicion', 30)->nullable()->after('tipo_material'); // compra | donacion | canje
            $table->string('recibido_por', 100)->nullable()->after('valor');
            $table->date('fecha_ingreso')->nullable()->after('fecha_adquisicion');
        });
    }

    public function down(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->dropColumn(['numero_entrada', 'paginas', 'medio_adquisicion', 'recibido_por', 'fecha_ingreso']);
        });
    }
};
