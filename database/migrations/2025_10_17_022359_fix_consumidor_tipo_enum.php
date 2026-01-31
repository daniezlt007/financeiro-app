<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar o enum consumidor_tipo para os valores corretos
        DB::statement("ALTER TABLE vendas MODIFY COLUMN consumidor_tipo ENUM(
            'CONSUMIDOR FINAL',
            'PARCEIRO FATURADO', 
            'PARCEIRO PRÉ-PAGO',
            'CONTRATO CORPORATIVO',
            'CORTESIA FUNCIONÁRIO'
        ) NOT NULL DEFAULT 'CONSUMIDOR FINAL'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o enum original
        DB::statement("ALTER TABLE vendas MODIFY COLUMN consumidor_tipo ENUM(
            'VAREJO',
            'ATACADO',
            'OUTRO'
        ) NOT NULL DEFAULT 'VAREJO'");
    }
};