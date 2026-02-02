<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{Pagamento,Venda,Servico};

class PagamentoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Pagamento::with(['venda.itens.servico', 'venda.itens.produto'])
            ->when(!$user->is_admin && $user->empresa_id, function ($q) use ($user) {
                $q->whereHas('venda', fn($q) => $q->where('empresa_id', $user->empresa_id));
            });
        
        // Filtro por serviço
        if ($request->filled('servico_id')) {
            $query->whereHas('venda.itens', function($q) use ($request) {
                $q->where('tipo_item', 'SERVICO')
                  ->where('servico_id', $request->servico_id);
            });
        }
        
        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtro por parceiro
        if ($request->filled('parceiro')) {
            $query->whereHas('venda', function($q) use ($request) {
                $q->where('parceiro', $request->parceiro);
            });
        }
        
        // Busca global
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%")
                  ->orWhere('forma_pagamento', 'like', "%{$search}%")
                  ->orWhere('valor', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('venda', function($q) use ($search) {
                      $q->where('cliente_nome_completo', 'like', "%{$search}%")
                        ->orWhere('cliente_placa', 'like', "%{$search}%")
                        ->orWhereHas('itens.servico', function($q) use ($search) {
                            $q->where('tipo_servico', 'like', "%{$search}%");
                        });
                  });
            });
        }
        
        $data = $query->latest('data')->paginate(20)->withQueryString();

        $servicos = Servico::select('id','tipo_servico as nome')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->orderBy('tipo_servico')->get();
        
        return Inertia::render('Pagamentos/Index', [
            'data' => $data,
            'filters' => $request->only(['search', 'servico_id', 'status', 'parceiro']),
            'servicos' => $servicos,
            'parceiros' => Venda::getParceirosDisponiveis()
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $vendas = Venda::query()
            ->when(!$user->is_admin && $user->empresa_id, fn($q) => $q->where('empresa_id', $user->empresa_id))
            ->latest()
            ->get();
        return Inertia::render('Pagamentos/Create', ['vendas' => $vendas]);
    }

    public function show(Pagamento $pagamento)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->empresa_id && ($pagamento->venda->empresa_id ?? null) !== $user->empresa_id) {
            abort(403, 'Acesso negado.');
        }
        $pagamento->load(['venda.itens.servico', 'venda.itens.produto']);
        return Inertia::render('Pagamentos/Show', ['pagamento' => $pagamento]);
    }

    public function edit(Pagamento $pagamento)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->empresa_id && ($pagamento->venda->empresa_id ?? null) !== $user->empresa_id) {
            abort(403, 'Acesso negado.');
        }
        $vendas = Venda::query()
            ->when(!$user->is_admin && $user->empresa_id, fn($q) => $q->where('empresa_id', $user->empresa_id))
            ->latest()
            ->get();
        return Inertia::render('Pagamentos/Edit', ['pagamento' => $pagamento, 'vendas' => $vendas]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'data' => 'required|date',
            'forma_pagamento' => 'required|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'valor' => 'required|numeric|min:0',
            'status' => 'required|in:PAGO,PENDENTE,CANCELADO',
        ]);
        
        $validated['user_id'] = $user->id;

        if (!$user->is_admin && $user->empresa_id) {
            $venda = Venda::findOrFail($validated['venda_id']);
            if ($venda->empresa_id !== $user->empresa_id) {
                abort(403, 'Acesso negado.');
            }
        }

        Pagamento::create($validated);
        return back()->with('success','Pagamento registrado com sucesso!');
    }

    public function update(Request $request, Pagamento $pagamento)
    {
        $user = auth()->user();
        if (!$user->is_admin && $user->empresa_id) {
            $pagamento->load('venda');
            if (($pagamento->venda->empresa_id ?? null) !== $user->empresa_id) {
                abort(403, 'Acesso negado.');
            }
        }

        $validated = $request->validate([
            'venda_id' => 'required|exists:vendas,id',
            'data_pagamento' => 'required|date',
            'forma_pagamento' => 'required|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'valor' => 'required|numeric|min:0',
            'status' => 'required|in:PAGO,PENDENTE,CANCELADO',
        ]);
        
        // Mapear data_pagamento para data
        $validated['data'] = $validated['data_pagamento'];
        unset($validated['data_pagamento']);
        
        // Adicionar user_id (quem editou o pagamento)
        $validated['user_id'] = $user->id;
        
        $pagamento->update($validated);
        return back()->with('success','Pagamento atualizado com sucesso!');
    }

    /**
     * Exibe a página de baixa em lote de pagamentos pendentes
     */
    public function baixaLote(Request $request)
    {
        $user = auth()->user();
        $query = Pagamento::with(['venda.itens.servico', 'venda.itens.produto'])
            ->where('status', 'PENDENTE')
            ->when(!$user->is_admin && $user->empresa_id, function ($q) use ($user) {
                $q->whereHas('venda', fn($q) => $q->where('empresa_id', $user->empresa_id));
            });
        
        // Filtro por data de início
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }
        
        // Filtro por data de fim
        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }
        
        // Filtro por parceiro
        if ($request->filled('parceiro')) {
            $query->whereHas('venda', function($q) use ($request) {
                $q->where('parceiro', $request->parceiro);
            });
        }
        
        // Busca global
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%")
                  ->orWhere('forma_pagamento', 'like', "%{$search}%")
                  ->orWhere('valor', 'like', "%{$search}%")
                  ->orWhereHas('venda', function($q) use ($search) {
                      $q->where('cliente_nome_completo', 'like', "%{$search}%")
                        ->orWhere('cliente_placa', 'like', "%{$search}%")
                        ->orWhere('parceiro', 'like', "%{$search}%");
                  });
            });
        }
        
        $data = $query->latest('data')->paginate(20)->withQueryString();
        
        return Inertia::render('Financeiro/BaixaLote', [
            'data' => $data,
            'filters' => $request->only(['search', 'data_inicio', 'data_fim', 'parceiro']),
            'parceiros' => Venda::getParceirosDisponiveis()
        ]);
    }

    /**
     * Processa a baixa em lote dos pagamentos selecionados
     */
    public function processarBaixaLote(Request $request)
    {
        $validated = $request->validate([
            'pagamento_ids' => 'required|array',
            'pagamento_ids.*' => 'required|exists:pagamentos,id',
        ]);

        $user = auth()->user();
        $pagamentos = Pagamento::with('venda')
            ->whereIn('id', $validated['pagamento_ids'])
            ->where('status', 'PENDENTE')
            ->when(!$user->is_admin && $user->empresa_id, function ($q) use ($user) {
                $q->whereHas('venda', fn($q) => $q->where('empresa_id', $user->empresa_id));
            })
            ->get();

        if ($pagamentos->isEmpty()) {
            return back()->with('error', 'Nenhum pagamento pendente selecionado para baixa.');
        }

        $baixados = 0;
        foreach ($pagamentos as $pagamento) {
            // Atualiza o status para PAGO
            // O Observer automaticamente atualizará a transação vinculada
            $pagamento->update([
                'status' => 'PAGO',
                'user_id' => $user->id, // Registra quem fez a baixa
            ]);
            $baixados++;
        }

        return back()->with('success', "{$baixados} pagamento(s) baixado(s) com sucesso!");
    }
}
