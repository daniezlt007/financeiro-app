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
            // Remover o campo string responsavel_venda
            $table->dropColumn('responsavel_venda');
            
            // Adicionar o campo funcionario_id como foreign key
            $table->foreignId('funcionario_id')->nullable()->constrained('funcionarios')->after('cliente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            // Remover o foreign key
            $table->dropForeign(['funcionario_id']);
            $table->dropColumn('funcionario_id');
            
            // Restaurar o campo string responsavel_venda
            $table->string('responsavel_venda')->nullable();
        });
    }
};
