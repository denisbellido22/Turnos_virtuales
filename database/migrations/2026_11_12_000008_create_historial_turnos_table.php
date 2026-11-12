<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas')->nullOnDelete()->cascadeOnUpdate();
            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);
            $table->string('accion', 30);
            $table->string('observacion', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['turno_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_turnos');
    }
};
