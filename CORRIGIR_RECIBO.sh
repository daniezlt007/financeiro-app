#!/bin/bash

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         CORRIGINDO RECIBO - https://deadsystem.com.br   ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

cd ~/public_html

echo "1️⃣  Verificando se arquivo existe..."
if [ -f resources/views/recibo_venda.blade.php ]; then
    echo "   ✅ recibo_venda.blade.php existe"
else
    echo "   ❌ recibo_venda.blade.php NÃO EXISTE - Precisa criar!"
fi
echo ""

echo "2️⃣  Verificando rota..."
if grep -q "vendas.recibo" routes/web.php 2>/dev/null; then
    echo "   ✅ Rota vendas.recibo existe"
else
    echo "   ❌ Rota vendas.recibo NÃO EXISTE - Precisa atualizar routes/web.php"
fi
echo ""

echo "3️⃣  Verificando método no controller..."
if grep -q "function recibo" app/Http/Controllers/VendaController.php 2>/dev/null; then
    echo "   ✅ Método recibo() existe"
else
    echo "   ❌ Método recibo() NÃO EXISTE - Precisa atualizar VendaController.php"
fi
echo ""

echo "4️⃣  Verificando se PDF está instalado..."
if grep -q "use Barryvdh\\DomPDF\\Facade\\Pdf" app/Http/Controllers/VendaController.php 2>/dev/null; then
    echo "   ✅ DomPDF está importado"
else
    echo "   ⚠️  Verificar se DomPDF está instalado"
fi
echo ""

echo "5️⃣  Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "   ✅ Caches limpos!"
echo ""

echo "6️⃣  Recriando cache de rotas..."
php artisan route:cache
echo "   ✅ Cache de rotas recriado!"
echo ""

echo "7️⃣  Listando rotas de vendas..."
php artisan route:list | grep vendas | grep recibo
echo ""

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║         ✅ DIAGNÓSTICO CONCLUÍDO                          ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""

echo "Teste agora: https://deadsystem.com.br/vendas/3/recibo"
echo ""













