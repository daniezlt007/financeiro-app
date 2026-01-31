<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\{Venda,Cliente,Servico,Pagamento};

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = now()->toDateString();
        $mesAtual = now()->month;
        $anoAtual = now()->year;
        
        // Métricas do dia
        $faturamentoHoje = Venda::whereDate('data', $hoje)->sum('valor_total');
        $vendasHoje = Venda::whereDate('data', $hoje)->count();
        
        // Métricas do mês
        $faturamentoMes = Venda::whereMonth('data', $mesAtual)
                              ->whereYear('data', $anoAtual)
                              ->sum('valor_total');
        $qtdVendasMes = Venda::whereMonth('data', $mesAtual)
                             ->whereYear('data', $anoAtual)
                             ->count();
        
        // Métricas gerais
        $totalClientes = Cliente::count();
        $totalProdutos = \App\Models\Produto::count();
        $totalServicos = Servico::count();
        
        // Vendas recentes - usar query builder direto sem Eloquent para evitar cache
        // Ordenar por created_at DESC primeiro, depois id DESC (mesmo que funciona no banco)
        $vendasRecentes = DB::table('vendas')
                              ->select('id', 'data', 'cliente_nome_completo', 'valor_total', 'forma_pagamento', 'created_at')
                              ->orderBy('created_at', 'desc')
                              ->orderBy('id', 'desc')
                              ->limit(5)
                              ->get();
        
        // Pagamentos pendentes
        $pagamentosPendentes = Pagamento::where('status', 'PENDENTE')->count();
        $valorPendente = Pagamento::where('status', 'PENDENTE')->sum('valor');

        return Inertia::render('Dashboard', [
            'cards' => [
                'faturamentoHoje' => (float)$faturamentoHoje,
                'vendasHoje' => (int)$vendasHoje,
                'faturamentoMes' => (float)$faturamentoMes,
                'qtdVendasMes' => (int)$qtdVendasMes,
                'totalClientes' => (int)$totalClientes,
                'totalProdutos' => (int)$totalProdutos,
                'totalServicos' => (int)$totalServicos,
                'pagamentosPendentes' => (int)$pagamentosPendentes,
                'valorPendente' => (float)$valorPendente,
            ],
            'vendasRecentes' => $vendasRecentes
        ]);
    }
}
