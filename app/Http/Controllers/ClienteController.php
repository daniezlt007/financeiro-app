<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function index()
    {
        $this->authorize('clientes.visualizar');
        $data = Cliente::latest()->paginate(15);
        return Inertia::render('Clientes/Index', ['data' => $data]);
    }

    public function create()
    {
        $this->authorize('clientes.criar');
        $user = auth()->user();
        $empresas = collect();
        
        // Se for admin, busca todas as empresas, senão só a empresa do usuário
        if ($user->is_admin ?? false) {
            $empresas = \App\Models\Empresa::select('id', 'nome_fantasia')->orderBy('nome_fantasia')->get();
        }
        
        return Inertia::render('Clientes/Create', [
            'empresas' => $empresas
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('clientes.criar');
        $user = auth()->user();
        
        // Definir regras de validação baseadas no tipo de usuário
        $validationRules = [
            'nome_completo'=>'required|string',
            'cpf_cnpj'=>'nullable|string',
            'telefone'=>'nullable|string',
            'email'=>'nullable|email',
            'endereco_completo'=>'nullable|string',
            'placa_veiculo'=>'nullable|string',
        ];
        
        // Se for admin, validar empresa_id do request
        if ($user->is_admin ?? false) {
            $validationRules['empresa_id'] = 'required|exists:empresas,id';
        }
        
        $validated = $request->validate($validationRules);
        
        // Se não for admin, usar empresa_id do usuário
        if (!($user->is_admin ?? false)) {
            if (!$user->empresa_id) {
                return redirect()->back()->withErrors(['empresa' => 'Usuário não possui empresa vinculada.']);
            }
            $validated['empresa_id'] = $user->empresa_id;
        }
        
        try {
            Cliente::create($validated);
            return redirect()->route('clientes.index')->with('success','Cliente criado com sucesso');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Erro ao criar cliente: ' . $e->getMessage()]);
        }
    }

    public function show(Cliente $cliente)
    {
        $this->authorize('clientes.visualizar');
        return Inertia::render('Clientes/Show', ['item' => $cliente]);
    }

    public function edit(Cliente $cliente)
    {
        $this->authorize('clientes.editar');
        return Inertia::render('Clientes/Edit', ['item' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $this->authorize('clientes.editar');
        $validated = $request->validate(['nome_completo'=>'required|string','cpf_cnpj'=>'nullable|string','telefone'=>'nullable|string','email'=>'nullable|email','endereco_completo'=>'nullable|string','placa_veiculo'=>'nullable|string']);
        $cliente->update($validated);
        return redirect()->route('clientes.index')->with('success','Atualizado');
    }

    public function destroy(Cliente $cliente)
    {
        $this->authorize('clientes.excluir');
        $cliente->delete();
        return redirect()->back()->with('success','Excluído');
    }

    public function buscarPorCpf(Request $request)
    {
        $cpf = $request->input('cpf');
        
        if (!$cpf) {
            return response()->json(['cliente' => null]);
        }

        // Remover formatação do CPF/CNPJ para busca
        $cpfLimpo = preg_replace('/[^0-9]/', '', $cpf);
        
        $cliente = Cliente::where('cpf_cnpj', 'like', "%{$cpfLimpo}%")
            ->orWhere('cpf_cnpj', 'like', "%{$cpf}%")
            ->first();

        if ($cliente) {
            return response()->json([
                'cliente' => [
                    'nome_completo' => $cliente->nome_completo,
                    'telefone' => $cliente->telefone,
                    'email' => $cliente->email,
                    'placa_veiculo' => $cliente->placa_veiculo,
                ]
            ]);
        }

        return response()->json(['cliente' => null]);
    }

    public function buscarPorNome(Request $request)
    {
        $nome = $request->input('nome');
        
        if (!$nome || strlen($nome) < 2) {
            return response()->json(['clientes' => []]);
        }

        $clientes = Cliente::where('nome_completo', 'like', "%{$nome}%")
            ->select('id', 'nome_completo', 'cpf_cnpj', 'telefone', 'placa_veiculo')
            ->orderBy('nome_completo')
            ->limit(10)
            ->get();

        return response()->json(['clientes' => $clientes]);
    }
}
