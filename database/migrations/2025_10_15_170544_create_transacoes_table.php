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
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('tipo', ['ENTRADA', 'SAIDA']);
            $table->enum('categoria', [
                // Entradas
                'VENDA', 'ENTRADA_MANUAL', 'OUTROS_RECEBIMENTOS',
                // Saídas
                'RETIRADA_SOCIO', 'DESPESA_FIXA', 'DESPESA_VARIAVEL', 
                'FORNECEDOR', 'SALARIO', 'IMPOSTO', 'OUTRAS_SAIDAS'
            ]);
            $table->date('data');
            $table->decimal('valor', 12, 2);
            $table->string('descricao');
            $table->text('observacoes')->nullable();
            $table->enum('forma_pagamento', ['DINHEIRO', 'PIX', 'CARTAO', 'BOLETO', 'TRANSFERENCIA', 'OUTRO'])->nullable();
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users'); // Quem registrou
            $table->timestamps();
            $table->index(['empresa_id', 'data']);
            $table->index(['tipo', 'categoria']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes');
    }
};
