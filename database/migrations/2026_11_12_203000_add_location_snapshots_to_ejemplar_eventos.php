<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejemplar_eventos', function (Blueprint $table) {
            $table->string('ubicacion_anterior_nombre', 100)->nullable()->after('ubicacion_anterior_id');
            $table->string('ubicacion_nueva_nombre', 100)->nullable()->after('ubicacion_nueva_id');
        });
    }

    public function down(): void
    {
        Schema::table('ejemplar_eventos', function (Blueprint $table) {
            $table->dropColumn(['ubicacion_anterior_nombre', 'ubicacion_nueva_nombre']);
        });
    }
};
