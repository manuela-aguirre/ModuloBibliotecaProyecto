<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('telefono', 20)->nullable()->after('numero_identificacion');
        $table->string('carrera', 100)->nullable()->after('telefono');
        $table->unsignedTinyInteger('semestre')->nullable()->after('carrera');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['telefono', 'carrera', 'semestre']);
    });
}
};
