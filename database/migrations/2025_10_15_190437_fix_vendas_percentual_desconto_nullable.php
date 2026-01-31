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
            // Garantir que percentual_desconto tenha um valor padrão de 0
            $table->decimal('percentual_desconto', 5, 2)->default(0)->change();
            
            // Garantir que comissao_venda também tenha um valor padrão
            $table->decimal('comissao_venda', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Reverter para nullable se necessário
            $table->decimal('percentual_desconto', 5, 2)->nullable()->change();
            $table->decimal('comissao_venda', 12, 2)->nullable()->change();
        });
    }
};