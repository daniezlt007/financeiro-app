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
        Schema::table('transacoes', function (Blueprint $table) {
            $table->enum('status', ['PAGO', 'PENDENTE'])->default('PAGO')->after('forma_pagamento');
        });
        
        // Atualizar transações existentes para PAGO por padrão
        DB::table('transacoes')->update(['status' => 'PAGO']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transacoes', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
