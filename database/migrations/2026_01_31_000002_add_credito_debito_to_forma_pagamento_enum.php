<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona CREDITO e DEBITO ao enum forma_pagamento em vendas, pagamentos e transacoes
        DB::statement("ALTER TABLE vendas MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','CREDITO','DEBITO','BOLETO','TRANSFERENCIA','OUTRO') DEFAULT 'OUTRO'");
        DB::statement("ALTER TABLE pagamentos MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','CREDITO','DEBITO','BOLETO','TRANSFERENCIA','OUTRO') DEFAULT 'OUTRO'");
        DB::statement("ALTER TABLE transacoes MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','CREDITO','DEBITO','BOLETO','TRANSFERENCIA','OUTRO') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vendas MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','BOLETO','TRANSFERENCIA','OUTRO') DEFAULT 'OUTRO'");
        DB::statement("ALTER TABLE pagamentos MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','BOLETO','TRANSFERENCIA','OUTRO') DEFAULT 'OUTRO'");
        DB::statement("ALTER TABLE transacoes MODIFY COLUMN forma_pagamento ENUM('DINHEIRO','PIX','CARTAO','BOLETO','TRANSFERENCIA','OUTRO') NULL");
    }
};
