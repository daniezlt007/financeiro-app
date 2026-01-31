<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->date('data');
            $table->enum('consumidor_tipo', ['VAREJO','ATACADO','OUTRO'])->default('VAREJO');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes');
            $table->string('responsavel_venda')->nullable();
            $table->decimal('valor_bruto',12,2)->default(0);
            $table->decimal('percentual_desconto',5,2)->default(0);
            $table->decimal('comissao_venda',12,2)->default(0);
            $table->enum('forma_pagamento',['DINHEIRO','PIX','CARTAO','BOLETO','TRANSFERENCIA','OUTRO'])->default('OUTRO');
            $table->string('numero_nota_fiscal',40)->nullable();
            $table->boolean('boleto_emitido')->default(false);
            $table->string('numero_pedido',40)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->index(['data']);
        });

        Schema::create('itens_venda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->enum('tipo_item',['PRODUTO','SERVICO']);
            $table->foreignId('produto_id')->nullable()->constrained('produtos');
            $table->foreignId('servico_id')->nullable()->constrained('servicos');
            $table->decimal('qtde',12,3)->default(1);
            $table->decimal('valor_unitario',12,2)->default(0);
            $table->timestamps();
        });

        Schema::create('pagamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade');
            $table->date('data');
            $table->enum('forma_pagamento',['DINHEIRO','PIX','CARTAO','BOLETO','TRANSFERENCIA','OUTRO'])->default('OUTRO');
            $table->decimal('valor',12,2);
            $table->enum('status',['PAGO','PENDENTE','CANCELADO'])->default('PENDENTE');
            $table->timestamps();
        });

        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->tinyInteger('mes');
            $table->smallInteger('ano');
            $table->decimal('meta_faturamento',12,2)->default(0);
            $table->integer('meta_producao_varejo')->default(0);
            $table->integer('meta_producao_atacado')->default(0);
            $table->timestamps();
            $table->unique(['empresa_id','mes','ano'], 'uq_metas_emp_mes_ano');
        });
    }
    public function down(): void {
        Schema::dropIfExists('metas');
        Schema::dropIfExists('pagamentos');
        Schema::dropIfExists('itens_venda');
        Schema::dropIfExists('vendas');
    }
};