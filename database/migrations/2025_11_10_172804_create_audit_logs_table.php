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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('model_type'); // Nome do model (ex: Cliente, Venda, etc)
            $table->unsignedBigInteger('model_id')->nullable(); // ID do registro
            $table->enum('action', ['created', 'updated', 'deleted', 'viewed']); // Ação realizada
            $table->text('old_values')->nullable(); // Valores antigos (JSON)
            $table->text('new_values')->nullable(); // Valores novos (JSON)
            $table->string('ip_address')->nullable(); // IP do usuário
            $table->string('user_agent')->nullable(); // Navegador/dispositivo
            $table->timestamps();
            
            // Índices para melhorar performance
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
