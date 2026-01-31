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
            if (!Schema::hasColumn('vendas', 'cliente_chassi')) {
                $table->string('cliente_chassi')->nullable()->after('cliente_placa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            if (Schema::hasColumn('vendas', 'cliente_chassi')) {
                $table->dropColumn('cliente_chassi');
            }
        });
    }
};
