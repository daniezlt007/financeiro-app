<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Transacao;
use App\Models\Empresa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransacaoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Transacao::with(['empresa', 'venda', 'user'])
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            });

        // Busca global em todos os campos
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('descricao', 'like', "%{$search}%")
                  ->orWhere('observacoes', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%")
                  ->orWhere('valor', 'like', "%{$search}%")
                  ->orWhere('tipo', 'like', "%{$search}%")
                  ->orWhere('categoria', 'like', "%{$search}%")
                  ->orWhere('forma_pagamento', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('empresa', function($q) use ($search) {
                      $q->where('nome_fantasia', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }
        
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }
        
        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }

        $transacoes = $query->latest('data')->latest('id')->paginate(20)->withQueryString();

        return Inertia::render('Financeiro/Index', [
            'data' => $transacoes,
            'filtros' => $request->only(['tipo', 'categoria', 'status', 'forma_pagamento', 'data_inicio', 'data_fim', 'search']),
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
            'formas_pagamento' => Transacao::FORMAS_PAGAMENTO,
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Período padrão: mês atual
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        
        $query = Transacao::query()
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->whereBetween('data', [$dataInicio, $dataFim]);

        // Aplicar filtros adicionais
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        // Totais - considerar apenas transações PAGAS para o saldo
        $totalEntradas = (clone $query)->where('tipo', 'ENTRADA')->where('status', 'PAGO')->sum('valor');
        $totalSaidas = (clone $query)->where('tipo', 'SAIDA')->where('status', 'PAGO')->sum('valor');
        $saldo = $totalEntradas - $totalSaidas;

        // Por categoria - considerar apenas PAGAS
        $entradasPorCategoria = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        $saidasPorCategoria = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Transações recentes - mostrar TODAS (PAGAS e PENDENTES)
        $transacoesRecentes = (clone $query)
            ->with(['empresa', 'venda', 'user'])
            ->latest('data')
            ->latest('id')
            ->limit(10)
            ->get();

        // Pagamentos pendentes (a receber) - COM filtro de período
        $queryPendentes = \App\Models\Pagamento::where('status', 'PENDENTE')
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->whereHas('venda', function($q) use ($user) {
                    $q->where('empresa_id', $user->empresa_id);
                });
            });
        
        $pagamentosPendentes = (clone $queryPendentes)->sum('valor');
        $qtdPagamentosPendentes = (clone $queryPendentes)->count();

        return Inertia::render('Financeiro/Dashboard', [
            'resumo' => [
                'total_entradas' => $totalEntradas,
                'total_saidas' => $totalSaidas,
                'saldo' => $saldo,
                'total_pendente' => $pagamentosPendentes,
                'qtd_pendente' => $qtdPagamentosPendentes,
                'entradas_por_categoria' => $entradasPorCategoria,
                'saidas_por_categoria' => $saidasPorCategoria,
            ],
            'transacoes_recentes' => $transacoesRecentes,
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ],
            'filtros' => $request->only(['tipo', 'categoria', 'status', 'forma_pagamento']),
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
            'formas_pagamento' => Transacao::FORMAS_PAGAMENTO,
        ]);
    }

    public function show(Transacao $transacao)
    {
        $user = auth()->user();
        
        // Verificar permissão
        if (!$user->is_admin && $transacao->empresa_id !== $user->empresa_id) {
            abort(403, 'Você não tem permissão para visualizar esta transação.');
        }

        $transacaoData = $transacao->load(['empresa', 'venda', 'user']);
        
        // Garantir que os atributos sejam incluídos
        $transacaoArray = $transacaoData->toArray();
        $transacaoArray['comprovante_url'] = $transacao->comprovante_url;
        
        return Inertia::render('Financeiro/Show', [
            'transacao' => $transacaoArray,
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        $empresas = $user->is_admin 
            ? Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get()
            : [];

        return Inertia::render('Financeiro/Create', [
            'empresas' => $empresas,
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
            'formas_pagamento' => Transacao::FORMAS_PAGAMENTO,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'tipo' => 'required|in:ENTRADA,SAIDA',
            'categoria' => 'required|string',
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'observacoes' => 'nullable|string',
            'forma_pagamento' => 'nullable|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'empresa_id' => $user->is_admin ? 'required|exists:empresas,id' : 'nullable',
            'comprovante_pagamento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        // Se não for admin, usar empresa do usuário
        if (!$user->is_admin) {
            $validated['empresa_id'] = $user->empresa_id;
        }

        $validated['user_id'] = $user->id;
        
        // Transações manuais sempre são PAGAS (não são pendentes)
        $validated['status'] = 'PAGO';

        // Processar upload do comprovante se fornecido
        if ($request->hasFile('comprovante_pagamento')) {
            $file = $request->file('comprovante_pagamento');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('comprovantes', $filename, 'public');
            $validated['comprovante_pagamento'] = $filename;
        }

        Transacao::create($validated);

        return redirect()->route('financeiro.index')->with('success', 'Transação registrada com sucesso!');
    }

    public function edit(Transacao $transacao)
    {
        $user = auth()->user();
        
        // Verificar permissão
        if (!$user->is_admin && $transacao->empresa_id !== $user->empresa_id) {
            abort(403, 'Você não tem permissão para editar esta transação.');
        }

        $empresas = $user->is_admin 
            ? Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get()
            : [];

        $transacaoData = $transacao->load(['empresa', 'venda', 'user']);
        
        // Garantir que os atributos sejam incluídos
        $transacaoArray = $transacaoData->toArray();
        $transacaoArray['comprovante_url'] = $transacao->comprovante_url;
        
        return Inertia::render('Financeiro/Edit', [
            'transacao' => $transacaoArray,
            'empresas' => $empresas,
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
            'formas_pagamento' => Transacao::FORMAS_PAGAMENTO,
            'errors' => []
        ]);
    }

    public function update(Request $request, Transacao $transacao)
    {
        $user = auth()->user();
        
        // Verificar permissão
        if (!$user->is_admin && $transacao->empresa_id !== $user->empresa_id) {
            abort(403, 'Você não tem permissão para editar esta transação.');
        }

        // Não permitir editar transações vinculadas a vendas
        if ($transacao->venda_id) {
            return back()->withErrors(['error' => 'Transações vinculadas a vendas não podem ser editadas manualmente.']);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:ENTRADA,SAIDA',
            'categoria' => 'required|string',
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'required|string|max:255',
            'observacoes' => 'nullable|string',
            'forma_pagamento' => 'nullable|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'empresa_id' => $user->is_admin ? 'required|exists:empresas,id' : 'nullable',
            'comprovante_pagamento' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ], [
            'tipo.required' => 'O tipo da transação é obrigatório.',
            'tipo.in' => 'O tipo deve ser ENTRADA ou SAIDA.',
            'categoria.required' => 'A categoria é obrigatória.',
            'data.required' => 'A data é obrigatória.',
            'data.date' => 'A data deve ser válida.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'O valor deve ser numérico.',
            'valor.min' => 'O valor deve ser maior que zero.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'forma_pagamento.in' => 'Forma de pagamento inválida.',
            'empresa_id.required' => 'A empresa é obrigatória.',
            'empresa_id.exists' => 'A empresa selecionada não existe.',
            'comprovante_pagamento.file' => 'O comprovante deve ser um arquivo.',
            'comprovante_pagamento.mimes' => 'O comprovante deve ser PDF, JPG, JPEG ou PNG.',
            'comprovante_pagamento.max' => 'O comprovante deve ter no máximo 5MB.',
        ]);

        // Se não for admin, manter empresa original
        if (!$user->is_admin) {
            unset($validated['empresa_id']);
        }

        // Processar upload do comprovante se fornecido
        if ($request->hasFile('comprovante_pagamento')) {
            // Deletar comprovante antigo se existir
            if ($transacao->hasComprovante()) {
                $transacao->deleteComprovante();
            }
            
            $file = $request->file('comprovante_pagamento');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('comprovantes', $filename, 'public');
            $validated['comprovante_pagamento'] = $filename;
        }

        $transacao->update($validated);

        return redirect()->route('financeiro.index')->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(Transacao $transacao)
    {
        $user = auth()->user();
        
        // Verificar permissão
        if (!$user->is_admin && $transacao->empresa_id !== $user->empresa_id) {
            abort(403, 'Você não tem permissão para excluir esta transação.');
        }

        // Não permitir excluir transações vinculadas a vendas
        if ($transacao->venda_id) {
            return back()->withErrors(['error' => 'Transações vinculadas a vendas não podem ser excluídas manualmente.']);
        }

        // Deletar comprovante se existir
        if ($transacao->hasComprovante()) {
            $transacao->deleteComprovante();
        }

        $transacao->delete();

        return redirect()->route('financeiro.index')->with('success', 'Transação excluída com sucesso!');
    }

    public function relatorios(Request $request)
    {
        $user = auth()->user();
        
        // Período padrão: mês atual
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        
        $query = Transacao::query()
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->whereBetween('data', [$dataInicio, $dataFim]);

        // Aplicar filtros adicionais
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        // Totais - considerar apenas transações PAGAS para o saldo
        $totalEntradas = (clone $query)->where('tipo', 'ENTRADA')->where('status', 'PAGO')->sum('valor');
        $totalSaidas = (clone $query)->where('tipo', 'SAIDA')->where('status', 'PAGO')->sum('valor');
        $saldo = $totalEntradas - $totalSaidas;

        // Entradas PAGAS por categoria
        $entradasPorCategoria = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Saídas PAGAS por categoria
        $saidasPorCategoria = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Pagamentos pendentes (a receber) - COM filtro de período
        $queryPendentes = \App\Models\Pagamento::where('status', 'PENDENTE')
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->whereHas('venda', function($q) use ($user) {
                    $q->where('empresa_id', $user->empresa_id);
                });
            });
        
        $pagamentosPendentes = (clone $queryPendentes)->sum('valor');
        $qtdPagamentosPendentes = (clone $queryPendentes)->count();

        // Entradas PENDENTES por categoria (dentro do período)
        $entradasPendentesPorCategoria = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PENDENTE')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Saídas PENDENTES por categoria (dentro do período)
        $saidasPendentesPorCategoria = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PENDENTE')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Total de entradas e saídas PENDENTES (dentro do período)
        $totalEntradasPendentes = (clone $query)->where('tipo', 'ENTRADA')->where('status', 'PENDENTE')->sum('valor');
        $totalSaidasPendentes = (clone $query)->where('tipo', 'SAIDA')->where('status', 'PENDENTE')->sum('valor');

        return Inertia::render('Financeiro/Relatorios', [
            'resumo' => [
                'total_entradas' => $totalEntradas,
                'total_saidas' => $totalSaidas,
                'saldo' => $saldo,
                'total_pendente' => $pagamentosPendentes,
                'qtd_pendente' => $qtdPagamentosPendentes,
                'total_entradas_pendentes' => $totalEntradasPendentes,
                'total_saidas_pendentes' => $totalSaidasPendentes,
                'entradas_por_categoria' => $entradasPorCategoria,
                'saidas_por_categoria' => $saidasPorCategoria,
                'entradas_pendentes_por_categoria' => $entradasPendentesPorCategoria,
                'saidas_pendentes_por_categoria' => $saidasPendentesPorCategoria,
            ],
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ],
            'filtros' => $request->only(['tipo', 'categoria', 'status', 'forma_pagamento']),
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
            'formas_pagamento' => Transacao::FORMAS_PAGAMENTO,
        ]);
    }

    public function dreFluxoCaixa(Request $request)
    {
        $user = auth()->user();
        
        // Período padrão: mês atual
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        
        $query = Transacao::query()
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->whereBetween('data', [$dataInicio, $dataFim]);

        // ========== DRE (Demonstração do Resultado do Exercício) ==========
        // DRE considera apenas transações PAGAS (regime de competência)
        
        // Receitas (Entradas PAGAS)
        $receitasVendas = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('categoria', 'VENDA')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $receitasOutras = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('categoria', '!=', 'VENDA')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $totalReceitas = $receitasVendas + $receitasOutras;
        
        // Despesas (Saídas PAGAS)
        $despesasOperacionais = (clone $query)
            ->where('tipo', 'SAIDA')
            ->whereIn('categoria', ['DESPESA_FIXA', 'DESPESA_VARIAVEL', 'FORNECEDOR', 'SALARIO'])
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $despesasImpostos = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('categoria', 'IMPOSTO')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $retiradas = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('categoria', 'RETIRADA_SOCIO')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $outrasDespesas = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('categoria', 'OUTRAS_SAIDAS')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $totalDespesas = $despesasOperacionais + $despesasImpostos + $retiradas + $outrasDespesas;
        
        // Resultado do Exercício (Lucro/Prejuízo)
        $resultadoExercicio = $totalReceitas - $totalDespesas;
        
        // DRE por categoria
        $dreReceitas = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });
        
        $dreDespesas = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });
        
        // ========== FLUXO DE CAIXA ==========
        // Fluxo de Caixa considera TODAS as transações (PAGAS e PENDENTES) - regime de caixa
        
        // Entradas de Caixa (todas as entradas, independente do status)
        $entradasCaixa = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->sum('valor');
        
        $entradasCaixaPagas = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $entradasCaixaPendentes = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PENDENTE')
            ->sum('valor');
        
        // Saídas de Caixa (todas as saídas, independente do status)
        $saidasCaixa = (clone $query)
            ->where('tipo', 'SAIDA')
            ->sum('valor');
        
        $saidasCaixaPagas = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->sum('valor');
        
        $saidasCaixaPendentes = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PENDENTE')
            ->sum('valor');
        
        // Saldo de Caixa (apenas PAGAS)
        $saldoCaixa = $entradasCaixaPagas - $saidasCaixaPagas;
        
        // Fluxo de Caixa por categoria (apenas PAGAS)
        $fluxoEntradas = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });
        
        $fluxoSaidas = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });
        
        return Inertia::render('Financeiro/DreFluxoCaixa', [
            'dre' => [
                'receitas_vendas' => $receitasVendas,
                'receitas_outras' => $receitasOutras,
                'total_receitas' => $totalReceitas,
                'despesas_operacionais' => $despesasOperacionais,
                'despesas_impostos' => $despesasImpostos,
                'retiradas' => $retiradas,
                'outras_despesas' => $outrasDespesas,
                'total_despesas' => $totalDespesas,
                'resultado_exercicio' => $resultadoExercicio,
                'receitas_por_categoria' => $dreReceitas,
                'despesas_por_categoria' => $dreDespesas,
            ],
            'fluxo_caixa' => [
                'entradas_total' => $entradasCaixa,
                'entradas_pagas' => $entradasCaixaPagas,
                'entradas_pendentes' => $entradasCaixaPendentes,
                'saidas_total' => $saidasCaixa,
                'saidas_pagas' => $saidasCaixaPagas,
                'saidas_pendentes' => $saidasCaixaPendentes,
                'saldo_caixa' => $saldoCaixa,
                'entradas_por_categoria' => $fluxoEntradas,
                'saidas_por_categoria' => $fluxoSaidas,
            ],
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ],
            'categorias_entrada' => Transacao::CATEGORIAS_ENTRADA,
            'categorias_saida' => Transacao::CATEGORIAS_SAIDA,
        ]);
    }

    public function lucroDespesas(Request $request)
    {
        $user = auth()->user();
        
        // Ano padrão: ano atual
        $ano = $request->input('ano', now()->year);
        
        $query = Transacao::query()
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->whereYear('data', $ano)
            ->where('status', 'PAGO'); // Apenas transações pagas
        
        // Calcular dados por mês
        $dadosMensais = [];
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        
        $totalReceitas = 0;
        $totalDespesas = 0;
        
        for ($mes = 1; $mes <= 12; $mes++) {
            // Receitas do mês (entradas pagas)
            $receitasMes = (clone $query)
                ->where('tipo', 'ENTRADA')
                ->whereMonth('data', $mes)
                ->sum('valor');
            
            // Despesas do mês (saídas pagas)
            $despesasMes = (clone $query)
                ->where('tipo', 'SAIDA')
                ->whereMonth('data', $mes)
                ->sum('valor');
            
            // Lucro líquido do mês (receitas - despesas)
            $lucroLiquidoMes = $receitasMes - $despesasMes;
            
            $dadosMensais[] = [
                'mes' => $mes,
                'mes_nome' => $meses[$mes],
                'receitas' => $receitasMes,
                'despesas' => $despesasMes,
                'lucro_liquido' => $lucroLiquidoMes,
            ];
            
            $totalReceitas += $receitasMes;
            $totalDespesas += $despesasMes;
        }
        
        // Lucro líquido total
        $lucroLiquidoTotal = $totalReceitas - $totalDespesas;
        
        // Anos disponíveis para filtro (últimos 5 anos)
        $anosDisponiveis = [];
        for ($i = 0; $i < 5; $i++) {
            $anoOption = now()->year - $i;
            $anosDisponiveis[] = $anoOption;
        }
        
        return Inertia::render('Financeiro/LucroDespesas', [
            'dados_mensais' => $dadosMensais,
            'totais' => [
                'total_receitas' => $totalReceitas,
                'total_despesas' => $totalDespesas,
                'lucro_liquido_total' => $lucroLiquidoTotal,
            ],
            'ano' => $ano,
            'anos_disponiveis' => $anosDisponiveis,
        ]);
    }

    public function gerarPdf(Request $request)
    {
        $user = auth()->user();
        
        // Período padrão: mês atual
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        
        $query = Transacao::query()
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->whereBetween('data', [$dataInicio, $dataFim]);

        // Aplicar filtros adicionais
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('forma_pagamento')) {
            $query->where('forma_pagamento', $request->forma_pagamento);
        }

        // Totais
        $totalEntradas = (clone $query)->where('tipo', 'ENTRADA')->where('status', 'PAGO')->sum('valor');
        $totalSaidas = (clone $query)->where('tipo', 'SAIDA')->where('status', 'PAGO')->sum('valor');
        $saldo = $totalEntradas - $totalSaidas;

        $entradasPorCategoria = (clone $query)
            ->where('tipo', 'ENTRADA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_ENTRADA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        $saidasPorCategoria = (clone $query)
            ->where('tipo', 'SAIDA')
            ->where('status', 'PAGO')
            ->select('categoria', DB::raw('SUM(valor) as total'))
            ->groupBy('categoria')
            ->get()
            ->map(function($item) {
                return [
                    'categoria' => $item->categoria,
                    'label' => Transacao::CATEGORIAS_SAIDA[$item->categoria] ?? $item->categoria,
                    'total' => $item->total
                ];
            });

        // Pagamentos pendentes (a receber) - COM filtro de período
        $queryPendentes = \App\Models\Pagamento::where('status', 'PENDENTE')
            ->whereBetween('data', [$dataInicio, $dataFim])
            ->when(!$user->is_admin, function($q) use ($user) {
                return $q->whereHas('venda', function($q) use ($user) {
                    $q->where('empresa_id', $user->empresa_id);
                });
            });
        
        $pagamentosPendentes = (clone $queryPendentes)->sum('valor');

        $totalEntradasPendentes = (clone $query)->where('tipo', 'ENTRADA')->where('status', 'PENDENTE')->sum('valor');
        $totalSaidasPendentes = (clone $query)->where('tipo', 'SAIDA')->where('status', 'PENDENTE')->sum('valor');

        $pdf = \PDF::loadView('relatorios.financeiro', [
            'resumo' => [
                'total_entradas' => $totalEntradas,
                'total_saidas' => $totalSaidas,
                'saldo' => $saldo,
                'total_pendente' => $pagamentosPendentes,
                'total_entradas_pendentes' => $totalEntradasPendentes,
                'total_saidas_pendentes' => $totalSaidasPendentes,
                'entradas_por_categoria' => $entradasPorCategoria,
                'saidas_por_categoria' => $saidasPorCategoria,
            ],
            'periodo' => [
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ],
            'filtros' => $request->only(['tipo', 'categoria', 'status', 'forma_pagamento']),
            'gerado_em' => now()->format('d/m/Y H:i:s'),
            'usuario' => $user->nome_completo ?? $user->name,
        ]);

        return $pdf->download('relatorio-financeiro-' . now()->format('Y-m-d') . '.pdf');
    }
}
