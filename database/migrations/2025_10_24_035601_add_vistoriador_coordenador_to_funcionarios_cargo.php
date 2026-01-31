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
            $table->enum('cargo', ['VENDEDOR', 'GERENTE', 'ADMINISTRATIVO', 'TECNICO', 'ATENDENTE', 'VISTORIADOR', 'COORDENADOR', 'OUTRO'])->default('VENDEDOR')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->enum('cargo', ['VENDEDOR', 'GERENTE', 'ADMINISTRATIVO', 'TECNICO', 'ATENDENTE', 'OUTRO'])->default('VENDEDOR')->change();
        });
    }
};
