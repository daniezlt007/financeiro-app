<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Recorrencia;

class RecorrenciaController extends Controller
{
    public function index()
    {
        $data = Recorrencia::latest()->paginate(20);
        return Inertia::render('Recorrencias/Index', ['data' => $data]);
    }

    public function create()
    { return Inertia::render('Recorrencias/Create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => 'required|in:RECEITA,DESPESA',
            'periodicidade' => 'required|in:DIARIO,SEMANAL,MENSAL',
            'valor' => 'required|numeric|min:0',
            'proxima_execucao' => 'required|date',
            'ativo' => 'boolean'
        ]);
        Recorrencia::create($validated);
        return redirect()->route('recorrencias.index')->with('success','Recorrência criada');
    }

    public function destroy(Recorrencia $recorrencia)
    {
        $recorrencia->delete();
        return back()->with('success','Recorrência excluída');
    }
}
