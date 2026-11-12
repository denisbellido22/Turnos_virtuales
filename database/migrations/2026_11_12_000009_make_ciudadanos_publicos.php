<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->string('nombre', 120)->nullable()->after('id');
            // Los turnos pueden solicitarse sin cuenta: el perfil queda solo con documento y nombre.
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('ciudadanos', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }
};
