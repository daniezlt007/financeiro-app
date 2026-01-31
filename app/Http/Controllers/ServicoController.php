<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Servico;

class ServicoController extends Controller
{
    public function index()
    {
        $data = Servico::latest()->paginate(15);
        return Inertia::render('Servicos/Index', ['data' => $data]);
    }

    public function create()
    {
        // Não precisa mais passar empresas, pois está fixo em empresa 1
        return Inertia::render('Servicos/Create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Definir regras de validação
        $validationRules = [
            'tipo_servico' => 'required|string',
            'preco_base' => 'nullable|numeric',
            'comissao_percentual' => 'nullable|numeric',
        ];
        
        $validated = $request->validate($validationRules);
        
        // Fixar empresa_id = 1 (por enquanto)
        $validated['empresa_id'] = 1;
        
        try {
            // Criar o serviço
            $servico = Servico::create($validated);
            
            // Associar apenas com empresa 1 (sem compartilhamento por enquanto)
            try {
                $servico->empresas()->sync([1]);
            } catch (\Exception $e) {
                // Se a tabela pivot não existir, apenas logar o erro mas não falhar
                \Log::warning('Tabela empresa_servico não encontrada: ' . $e->getMessage());
            }
            
            return redirect()->route('servicos.index')->with('success','Criado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao criar serviço: ' . $e->getMessage()]);
        }
    }

    public function show(Servico $servico)
    {
        return Inertia::render('Servicos/Show', ['item' => $servico]);
    }

    public function edit(Servico $servico)
    {
        try {
            // Carregar empresa proprietária
            $servico->load('empresa');
            
            // Não precisa mais passar empresas ou empresas_selecionadas
            return Inertia::render('Servicos/Edit', [
                'item' => $servico
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro no método edit do ServicoController: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('servicos.index')->withErrors(['error' => 'Erro ao carregar formulário de edição: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, Servico $servico)
    {
        $user = auth()->user();
        
        $validationRules = [
            'tipo_servico' => 'required|string',
            'preco_base' => 'nullable|numeric',
            'comissao_percentual' => 'nullable|numeric',
        ];
        
        $validated = $request->validate($validationRules);
        
        // Fixar empresa_id = 1 (por enquanto)
        $validated['empresa_id'] = 1;
        
        try {
            // Atualizar o serviço
            $servico->update($validated);
            
            // Associar apenas com empresa 1 (sem compartilhamento por enquanto)
            try {
                $servico->empresas()->sync([1]);
            } catch (\Exception $e) {
                // Se a tabela pivot não existir, apenas logar o erro mas não falhar
                \Log::warning('Tabela empresa_servico não encontrada: ' . $e->getMessage());
            }
            
            return redirect()->route('servicos.index')->with('success','Atualizado');
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar serviço: ' . $e->getMessage(), [
                'servico_id' => $servico->id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar serviço: ' . $e->getMessage()]);
        }
    }

    public function destroy(Servico $servico)
    {
        $servico->delete();
        return redirect()->back()->with('success','Excluído');
    }
}
