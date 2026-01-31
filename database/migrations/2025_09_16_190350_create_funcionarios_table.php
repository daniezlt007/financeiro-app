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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome_completo');
            $table->string('cpf', 14)->unique();
            $table->string('rg', 20)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('endereco_completo')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->date('data_admissao')->nullable();
            $table->enum('cargo', ['VENDEDOR', 'GERENTE', 'ADMINISTRATIVO', 'TECNICO', 'ATENDENTE', 'VISTORIADOR', 'COORDENADOR', 'OUTRO'])->default('VENDEDOR');
            $table->decimal('salario', 10, 2)->default(0);
            $table->enum('status', ['ATIVO', 'INATIVO', 'FERIAS', 'AFASTADO'])->default('ATIVO');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            $table->index(['empresa_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
