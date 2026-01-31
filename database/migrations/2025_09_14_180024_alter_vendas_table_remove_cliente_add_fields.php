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
        Schema::table('vendas', function (Blueprint $table) {
            // Verificar se o campo cliente_id existe antes de tentar removê-lo
            if (Schema::hasColumn('vendas', 'cliente_id')) {
                $table->dropForeign(['cliente_id']);
                $table->dropColumn('cliente_id');
            }
            
            // Adicionar campos de cliente diretamente na venda
            if (!Schema::hasColumn('vendas', 'cliente_nome_completo')) {
                $table->string('cliente_nome_completo')->after('responsavel_venda');
            }
            
            if (!Schema::hasColumn('vendas', 'cliente_cpf_cnpj')) {
                $table->string('cliente_cpf_cnpj')->nullable()->after('cliente_nome_completo');
            }
            
            if (!Schema::hasColumn('vendas', 'cliente_telefone')) {
                $table->string('cliente_telefone')->nullable()->after('cliente_cpf_cnpj');
            }
            
            if (!Schema::hasColumn('vendas', 'cliente_placa')) {
                $table->string('cliente_placa')->nullable()->after('cliente_telefone');
            }
            
            if (!Schema::hasColumn('vendas', 'consumidor_tipo')) {
                $table->enum('consumidor_tipo', [
                    'CONSUMIDOR FINAL',
                    'PARCEIRO FATURADO', 
                    'PARCEIRO PRÉ-PAGO',
                    'CONTRATO CORPORATIVO',
                    'CORTESIA FUNCIONÁRIO'
                ])->default('CONSUMIDOR FINAL')->after('cliente_placa');
            } else {
                // Se a coluna já existe, alterar o enum para os novos valores
                $table->enum('consumidor_tipo', [
                    'CONSUMIDOR FINAL',
                    'PARCEIRO FATURADO', 
                    'PARCEIRO PRÉ-PAGO',
                    'CONTRATO CORPORATIVO',
                    'CORTESIA FUNCIONÁRIO'
                ])->default('CONSUMIDOR FINAL')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Reverter as mudanças
            $table->dropColumn([
                'cliente_nome_completo',
                'cliente_cpf_cnpj', 
                'cliente_telefone',
                'cliente_placa',
                'consumidor_tipo'
            ]);
            
            // Recriar vínculo com cliente
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->after('funcionario_id');
        });
    }
};

