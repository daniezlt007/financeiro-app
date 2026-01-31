<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Funcionario;
use App\Models\Empresa;

class FuncionarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        $query = Funcionario::with('empresa');
        
        // Se não for admin, filtrar por empresa
        if (!($user->is_admin ?? false)) {
            $query->where('empresa_id', $user->empresa_id);
        }
        
        $data = $query->latest()->paginate(15);
        return Inertia::render('Funcionarios/Index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Sempre carregar as empresas para o combobox
        $empresas = Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        
        return Inertia::render('Funcionarios/Create', [
            'empresas' => $empresas
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Definir regras de validação
        $validationRules = [
            'empresa_id' => 'required|exists:empresas,id',
            'nome_completo' => 'required|string|max:255',
            'nome_extenso' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:30',
            'cargo' => 'required|in:VENDEDOR,GERENTE,ADMINISTRATIVO,TECNICO,ATENDENTE,VISTORIADOR,COORDENADOR,OUTRO',
            'status' => 'required|in:ATIVO,INATIVO,FERIAS,AFASTADO',
            'observacoes' => 'nullable|string',
        ];
        
        $validated = $request->validate($validationRules);
        
        try {
            \Log::info('Dados validados para criar funcionário:', $validated);
            $funcionario = Funcionario::create($validated);
            \Log::info('Funcionário criado com sucesso:', ['id' => $funcionario->id]);
            return redirect()->route('funcionarios.index')->with('success','Funcionário criado com sucesso');
        } catch (\Exception $e) {
            \Log::error('Erro ao criar funcionário:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['error' => 'Erro ao criar funcionário: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Funcionario $funcionario)
    {
        $funcionario->load('empresa');
        return Inertia::render('Funcionarios/Show', ['item' => $funcionario]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Funcionario $funcionario)
    {
        // Sempre carregar as empresas para o combobox
        $empresas = Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        
        return Inertia::render('Funcionarios/Edit', [
            'item' => $funcionario,
            'empresas' => $empresas
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Funcionario $funcionario)
    {
        $user = auth()->user();
        
        // Definir regras de validação
        $validationRules = [
            'empresa_id' => 'required|exists:empresas,id',
            'nome_completo' => 'required|string|max:255',
            'nome_extenso' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:30',
            'cargo' => 'required|in:VENDEDOR,GERENTE,ADMINISTRATIVO,TECNICO,ATENDENTE,VISTORIADOR,COORDENADOR,OUTRO',
            'status' => 'required|in:ATIVO,INATIVO,FERIAS,AFASTADO',
            'observacoes' => 'nullable|string',
        ];
        
        $validated = $request->validate($validationRules);
        
        try {
            $funcionario->update($validated);
            return redirect()->route('funcionarios.index')->with('success','Funcionário atualizado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao atualizar funcionário: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Funcionario $funcionario)
    {
        try {
            $funcionario->delete();
            return redirect()->back()->with('success','Funcionário excluído com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao excluir funcionário: ' . $e->getMessage()]);
        }
    }
}
