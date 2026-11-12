<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);
            $table->unsignedInteger('numero');
            $table->date('fecha');
            $table->foreignId('servicio_id')->constrained('servicios')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('area_id')->constrained('areas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('ciudadano_id')->constrained('ciudadanos')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('ventanilla_id')->nullable()->constrained('ventanillas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('llamado_at')->nullable();
            $table->timestamp('atendido_at')->nullable();
            $table->timestamp('finalizado_at')->nullable();
            $table->timestamps();

            $table->unique(['fecha', 'codigo']);
            $table->unique(['servicio_id', 'fecha', 'numero']);
            $table->index(['fecha', 'estado']);
            $table->index(['area_id', 'fecha', 'estado', 'created_at'], 'turnos_cola_idx');
            $table->index(['ciudadano_id', 'fecha', 'estado']);
            $table->index(['ventanilla_id', 'estado']);
            $table->index(['fecha', 'estado', 'llamado_at'], 'turnos_pantalla_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
