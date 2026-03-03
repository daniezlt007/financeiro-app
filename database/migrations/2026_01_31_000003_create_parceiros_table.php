<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parceiros')) {
            return;
        }

        Schema::create('parceiros', function (Blueprint $table) {
            $table->id();
            $table->string('nome_parceiro');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parceiros');
    }
};
