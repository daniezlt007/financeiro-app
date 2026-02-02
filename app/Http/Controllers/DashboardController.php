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
        $user = auth()->user();
        $empresaScope = !$user->is_admin && $user->empresa_id;
        $can = fn(string $perm) => $user->is_admin || $user->can($perm);

        $hoje = now()->toDateString();
        $mesAtual = now()->month;
        $anoAtual = now()->year;

        $vendasBase = Venda::query()->when($empresaScope, fn($q) => $q->where('empresa_id', $user->empresa_id));

        $faturamentoHoje = $can('dashboard.faturamento') ? (clone $vendasBase)->whereDate('data', $hoje)->sum('valor_total') : 0;
        $vendasHoje = $can('dashboard.faturamento') ? (clone $vendasBase)->whereDate('data', $hoje)->count() : 0;
        $faturamentoMes = $can('dashboard.faturamento') ? (clone $vendasBase)->whereMonth('data', $mesAtual)->whereYear('data', $anoAtual)->sum('valor_total') : 0;
        $qtdVendasMes = $can('dashboard.faturamento') ? (clone $vendasBase)->whereMonth('data', $mesAtual)->whereYear('data', $anoAtual)->count() : 0;

        $totalClientes = $can('dashboard.clientes') ? Cliente::query()->when($empresaScope, fn($q) => $q->where('empresa_id', $user->empresa_id))->count() : 0;
        $totalProdutos = $can('dashboard.produtos') ? \App\Models\Produto::query()->when($empresaScope, fn($q) => $q->where('empresa_id', $user->empresa_id))->count() : 0;
        $totalServicos = $can('dashboard.servicos') ? Servico::query()->when($empresaScope, fn($q) => $q->where('empresa_id', $user->empresa_id))->count() : 0;

        $pagamentosQuery = Pagamento::query()->where('status', 'PENDENTE');
        if ($empresaScope) {
            $pagamentosQuery->whereHas('venda', fn($q) => $q->where('empresa_id', $user->empresa_id));
        }
        $pagamentosPendentes = $can('dashboard.pendentes') ? $pagamentosQuery->count() : 0;
        $valorPendente = $can('dashboard.pendentes') ? (clone $pagamentosQuery)->sum('valor') : 0;

        $vendasRecentesQuery = DB::table('vendas');
        if ($empresaScope) {
            $vendasRecentesQuery->where('empresa_id', $user->empresa_id);
        }
        $vendasRecentes = $can('dashboard.vendas_recentes')
            ? $vendasRecentesQuery->select('id', 'data', 'cliente_nome_completo', 'valor_total', 'forma_pagamento', 'created_at')
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(5)->get()
            : collect();

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
            'vendasRecentes' => $vendasRecentes,
        ]);
    }
}
