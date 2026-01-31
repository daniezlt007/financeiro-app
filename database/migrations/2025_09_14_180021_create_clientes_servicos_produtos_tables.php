<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome_completo');
            $table->string('cpf_cnpj',20)->nullable();
            $table->string('endereco_completo')->nullable();
            $table->string('telefone',30)->nullable();
            $table->string('email')->nullable();
            $table->string('codigo_cliente',60)->nullable();
            $table->string('placa_veiculo',10)->nullable();
            $table->timestamps();
            $table->unique(['empresa_id','cpf_cnpj']);
        });

        Schema::create('servicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('tipo_servico');
            $table->string('descricao')->nullable();
            $table->decimal('preco_base',12,2)->default(0);
            $table->decimal('comissao_percentual',5,2)->default(0);
            $table->timestamps();
            $table->unique(['empresa_id','tipo_servico']);
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('nome');
            $table->string('sku',80)->nullable();
            $table->decimal('preco',12,2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('servicos');
        Schema::dropIfExists('clientes');
    }
};