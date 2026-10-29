<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // usuario | administrador (el administrador es el bibliotecario)
            $table->string('rol', 20)->default('usuario')->after('numero_identificacion');
            $table->unsignedTinyInteger('limite_prestamos')->default(3)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'limite_prestamos']);
        });
    }
};