<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rol_id')->nullable()->after('id')
                ->constrained('roles')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('area_id')->nullable()->after('rol_id')
                ->constrained('areas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ventanilla_id')->nullable()->after('area_id')
                ->constrained('ventanillas')->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('activo')->default(true)->after('password');

            $table->index(['rol_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->dropForeign(['area_id']);
            $table->dropForeign(['ventanilla_id']);
            $table->dropIndex(['rol_id', 'activo']);
            $table->dropColumn(['rol_id', 'area_id', 'ventanilla_id', 'activo']);
        });
    }
};
