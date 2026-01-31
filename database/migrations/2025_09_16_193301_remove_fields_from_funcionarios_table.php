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
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn([
                'cpf',
                'rg', 
                'email',
                'endereco_completo',
                'data_nascimento',
                'data_admissao',
                'salario'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->string('cpf', 14)->unique();
            $table->string('rg', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('endereco_completo')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->date('data_admissao')->nullable();
            $table->decimal('salario', 10, 2)->default(0);
        });
    }
};
