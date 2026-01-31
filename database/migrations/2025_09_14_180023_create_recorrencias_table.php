<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->enum('tipo',['RECEITA','DESPESA']);
            $table->enum('periodicidade',['DIARIO','SEMANAL','MENSAL']);
            $table->decimal('valor',12,2);
            $table->date('proxima_execucao');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('recorrencias'); }
};