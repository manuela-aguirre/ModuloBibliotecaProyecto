<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->string('coleccion', 30)->default('general')->after('tipo_material');
        });
    }

    public function down(): void
    {
        Schema::table('ejemplares', function (Blueprint $table) {
            $table->dropColumn('coleccion');
        });
    }
};
