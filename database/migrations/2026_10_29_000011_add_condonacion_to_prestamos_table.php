<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->decimal('multa_original', 10, 2)->nullable()->after('multa_pagada');
            $table->unsignedTinyInteger('condonacion_porcentaje')->default(0)->after('multa_original');
            $table->string('condonacion_motivo', 255)->nullable()->after('condonacion_porcentaje');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn(['multa_original', 'condonacion_porcentaje', 'condonacion_motivo']);
        });
    }
};
