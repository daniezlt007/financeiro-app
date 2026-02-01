<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\{Venda,Servico,Produto,ItemVenda};
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class VendaController extends Controller
{
    public function index(Request $request)
    {
        $query = Venda::with(['funcionario', 'pagamentos']);
        
        // Busca em todos os campos
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cliente_nome_completo', 'like', "%{$search}%")
                  ->orWhere('cliente_placa', 'like', "%{$search}%")
                  ->orWhere('cliente_cpf_cnpj', 'like', "%{$search}%")
                  ->orWhere('data', 'like', "%{$search}%")
                  ->orWhere('valor_total', 'like', "%{$search}%")
                  ->orWhere('forma_pagamento', 'like', "%{$search}%")
                  ->orWhere('parceiro', 'like', "%{$search}%")
                  ->orWhereHas('funcionario', function($q) use ($search) {
                      $q->where('nome_completo', 'like', "%{$search}%");
                  })
                  ->orWhereHas('pagamentos', function($q) use ($search) {
                      $q->where('status', 'like', "%{$search}%");
                  });
            });
        }
        
        $vendas = $query->latest('data')->paginate(15)->withQueryString();
        
        return Inertia::render('Vendas/Index', ['data' => $vendas]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Buscar serviços: próprios da empresa OU compartilhados com a empresa
        $servicos = Servico::select('servicos.id','servicos.tipo_servico','servicos.preco_base')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                // Serviços da empresa OU serviços compartilhados com a empresa
                return $q->where(function($query) use ($user) {
                    $query->where('servicos.empresa_id', $user->empresa_id)
                          ->orWhereHas('empresas', function($q) use ($user) {
                              $q->where('empresas.id', $user->empresa_id);
                          });
                });
            })
            ->orderBy('tipo_servico')->get();
            
        $produtos = Produto::select('id','nome','preco')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->orderBy('nome')->get();
            
        $funcionarios = \App\Models\Funcionario::select('id','nome_completo')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->where('status', 'ATIVO')
            ->orderBy('nome_completo')->get();
        
        $parceiros = \App\Models\Venda::getParceirosDisponiveis();
        
        return Inertia::render('Vendas/Create', [
            'funcionarios' => $funcionarios,
            'servicos' => $servicos,
            'produtos' => $produtos,
            'parceiros' => $parceiros,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'data' => 'required|date',
            'cliente_nome_completo' => 'required|string|max:255',
            'cliente_cpf_cnpj' => 'nullable|string|max:20',
            'cliente_telefone' => 'nullable|string|max:20',
            'cliente_placa' => 'nullable|string|max:10',
            'cliente_chassi' => 'nullable|string|max:255',
            'parceiro' => 'nullable|string|max:255',
            'consumidor_tipo' => 'required|in:CONSUMIDOR FINAL,PARCEIRO FATURADO,PARCEIRO PRÉ-PAGO,CONTRATO CORPORATIVO,CORTESIA FUNCIONÁRIO',
            'funcionario_id' => 'nullable|integer|exists:funcionarios,id',
            'valor_total' => 'required|numeric',
            'percentual_desconto' => 'nullable|numeric|min:0|max:100',
            'comissao_venda' => 'nullable|numeric',
            'forma_pagamento' => 'required|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'itens' => 'array|min:1',
            'itens.*.tipo_item' => 'required|in:SERVICO,PRODUTO',
            'itens.*.id' => 'required|integer',
            'itens.*.qtde' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        // Adicionar empresa_id aos dados da venda
        $vendaData = collect($validated)->except('itens')->toArray();
        
        // Verificar se o usuário tem empresa_id, senão usar empresa padrão
        if (!$user->empresa_id) {
            $empresaPadrao = \App\Models\Empresa::first();
            if (!$empresaPadrao) {
                return back()->withErrors(['error' => 'Nenhuma empresa cadastrada no sistema.']);
            }
            $vendaData['empresa_id'] = $empresaPadrao->id;
        } else {
            $vendaData['empresa_id'] = $user->empresa_id;
        }
        
        // Garantir que campos numéricos tenham valores padrão
        $vendaData['percentual_desconto'] = $vendaData['percentual_desconto'] ?? 0;
        $vendaData['comissao_venda'] = $vendaData['comissao_venda'] ?? 0;
        
        // Adicionar user_id (quem salvou a venda) - apenas se a coluna existir
        if (Schema::hasColumn('vendas', 'user_id')) {
            $vendaData['user_id'] = $user->id;
        }

        try {
            $venda = Venda::create($vendaData);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar venda:', [
                'error' => $e->getMessage(),
                'data' => $vendaData,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erro ao salvar venda: ' . $e->getMessage()]);
        }
        
        // Criar itens da venda
        foreach ($validated['itens'] as $it) {
            ItemVenda::create([
                'venda_id' => $venda->id,
                'tipo_item' => $it['tipo_item'],
                'servico_id' => $it['tipo_item']==='SERVICO' ? $it['id'] : null,
                'produto_id' => $it['tipo_item']==='PRODUTO' ? $it['id'] : null,
                'qtde' => $it['qtde'],
                'valor_unitario' => $it['valor_unitario'],
            ]);
        }
        
        // Criar pagamento automaticamente
        $pagamentoData = [
            'venda_id' => $venda->id,
            'data' => $validated['data'],
            'forma_pagamento' => $validated['forma_pagamento'],
            'valor' => $validated['valor_total'],
            'status' => 'PENDENTE',
        ];
        
        // Adicionar user_id se a coluna existir (verificar se foi adicionada pela migration)
        // Se a migration foi executada, adicionar user_id
        if (Schema::hasColumn('pagamentos', 'user_id')) {
            $pagamentoData['user_id'] = $user->id;
        }
        
        try {
            \App\Models\Pagamento::create($pagamentoData);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar pagamento:', [
                'error' => $e->getMessage(),
                'venda_id' => $venda->id,
                'pagamento_data' => $pagamentoData,
                'trace' => $e->getTraceAsString()
            ]);
            // Retornar erro ao invés de continuar silenciosamente
            return back()->withErrors(['error' => 'Erro ao criar pagamento: ' . $e->getMessage()])->withInput();
        }
        
        return redirect()->route('vendas.index')->with('success','Venda registrada e pagamento criado com sucesso!');
    }

    public function show(Venda $venda)
    {
        $venda->load(['funcionario', 'itens.servico', 'itens.produto', 'pagamentos']);
        return Inertia::render('Vendas/Show', ['item' => $venda]);
    }

    public function recibo(Venda $venda)
    {
        $venda->load(['funcionario', 'itens.servico', 'itens.produto', 'pagamentos', 'user']);
        
        // Criar hash único para o recibo (baseado em dados da venda + data)
        $hashData = $venda->id . $venda->data . $venda->valor_total . $venda->cliente_nome_completo . now()->toDateTimeString();
        $hash = substr(hash('sha256', $hashData), 0, 16); // Primeiros 16 caracteres do SHA-256
        
        $pdf = Pdf::loadView('recibo_venda', [
            'venda' => $venda,
            'hash_verificacao' => $hash
        ]);
        
        return $pdf->download('recibo_venda_' . $venda->id . '.pdf');
    }

    public function edit(Venda $venda)
    {
        $user = auth()->user();
        
        // Buscar serviços: próprios da empresa OU compartilhados com a empresa
        $servicos = Servico::select('servicos.id','servicos.tipo_servico','servicos.preco_base')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                // Serviços da empresa OU serviços compartilhados com a empresa
                return $q->where(function($query) use ($user) {
                    $query->where('servicos.empresa_id', $user->empresa_id)
                          ->orWhereHas('empresas', function($q) use ($user) {
                              $q->where('empresas.id', $user->empresa_id);
                          });
                });
            })
            ->orderBy('tipo_servico')->get();
            
        $produtos = Produto::select('id','nome','preco')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->orderBy('nome')->get();
            
        $funcionarios = \App\Models\Funcionario::select('id','nome_completo')
            ->when(!($user->is_admin ?? false), function($q) use ($user) {
                return $q->where('empresa_id', $user->empresa_id);
            })
            ->where('status', 'ATIVO')
            ->orderBy('nome_completo')->get();
        
        $parceiros = \App\Models\Venda::getParceirosDisponiveis();
        
        $venda->load(['itens.servico', 'itens.produto']);
        
        return Inertia::render('Vendas/Edit', [
            'item' => $venda,
            'funcionarios' => $funcionarios,
            'servicos' => $servicos,
            'produtos' => $produtos,
            'parceiros' => $parceiros,
        ]);
    }

    public function update(Request $request, Venda $venda)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'data' => 'required|date',
            'cliente_nome_completo' => 'required|string|max:255',
            'cliente_cpf_cnpj' => 'nullable|string|max:20',
            'cliente_telefone' => 'nullable|string|max:20',
            'cliente_placa' => 'nullable|string|max:10',
            'cliente_chassi' => 'nullable|string|max:255',
            'parceiro' => 'nullable|string|max:255',
            'consumidor_tipo' => 'required|in:CONSUMIDOR FINAL,PARCEIRO FATURADO,PARCEIRO PRÉ-PAGO,CONTRATO CORPORATIVO,CORTESIA FUNCIONÁRIO',
            'funcionario_id' => 'nullable|integer|exists:funcionarios,id',
            'valor_total' => 'required|numeric',
            'percentual_desconto' => 'nullable|numeric|min:0|max:100',
            'comissao_venda' => 'nullable|numeric',
            'forma_pagamento' => 'required|in:DINHEIRO,PIX,CREDITO,DEBITO,BOLETO,TRANSFERENCIA,OUTRO',
            'itens' => 'array|min:1',
            'itens.*.tipo_item' => 'required|in:SERVICO,PRODUTO',
            'itens.*.id' => 'required|integer',
            'itens.*.qtde' => 'required|numeric|min:1',
            'itens.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        // Atualizar dados da venda
        $vendaData = collect($validated)->except('itens')->toArray();
        
        // Garantir que campos numéricos tenham valores padrão
        $vendaData['percentual_desconto'] = $vendaData['percentual_desconto'] ?? 0;
        $vendaData['comissao_venda'] = $vendaData['comissao_venda'] ?? 0;
        
        // Atualizar user_id (quem editou a venda)
        $vendaData['user_id'] = $user->id;

        $venda->update($vendaData);
        
        // Excluir itens antigos
        ItemVenda::where('venda_id', $venda->id)->delete();
        
        // Criar novos itens
        foreach ($validated['itens'] as $it) {
            ItemVenda::create([
                'venda_id' => $venda->id,
                'tipo_item' => $it['tipo_item'],
                'servico_id' => $it['tipo_item']==='SERVICO' ? $it['id'] : null,
                'produto_id' => $it['tipo_item']==='PRODUTO' ? $it['id'] : null,
                'qtde' => $it['qtde'],
                'valor_unitario' => $it['valor_unitario'],
            ]);
        }
        
        // Atualizar pagamento vinculado
        $pagamento = \App\Models\Pagamento::where('venda_id', $venda->id)->first();
        if ($pagamento) {
            $pagamento->update([
                'data' => $validated['data'],
                'forma_pagamento' => $validated['forma_pagamento'],
                'valor' => $validated['valor_total'],
                'user_id' => $user->id, // Atualizar quem editou o pagamento
            ]);
        }
        
        // Atualizar transação financeira vinculada (se existir)
        $transacao = \App\Models\Transacao::where('venda_id', $venda->id)
            ->where('categoria', 'VENDA')
            ->first();
        if ($transacao) {
            $transacao->update([
                'data' => $validated['data'],
                'valor' => $validated['valor_total'],
                'forma_pagamento' => $validated['forma_pagamento'],
                'descricao' => 'Venda #' . $venda->id . ' - ' . ($validated['cliente_nome_completo'] ?? 'Cliente não informado'),
            ]);
        }
        
        return redirect()->route('vendas.index')->with('success','Venda atualizada com sucesso!');
    }

    public function destroy(Venda $venda)
    {
        // Excluir transações financeiras vinculadas
        \App\Models\Transacao::where('venda_id', $venda->id)->delete();
        
        // Excluir pagamentos vinculados
        \App\Models\Pagamento::where('venda_id', $venda->id)->delete();
        
        // Excluir itens vinculados
        ItemVenda::where('venda_id', $venda->id)->delete();
        
        // Excluir venda
        $venda->delete();
        
        return redirect()->route('vendas.index')->with('success','Venda, transações financeiras e pagamentos excluídos com sucesso!');
    }
}
