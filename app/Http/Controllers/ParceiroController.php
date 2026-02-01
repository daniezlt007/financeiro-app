<?php

namespace App\Http\Controllers;

use App\Models\Parceiro;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParceiroController extends Controller
{
    public function index(Request $request)
    {
        $query = Parceiro::orderBy('nome_parceiro');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nome_parceiro', 'like', "%{$search}%");
        }

        $data = $query->paginate(25)->withQueryString();

        return Inertia::render('Parceiros/Index', [
            'data' => $data,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Parceiros/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_parceiro' => 'required|string|max:255',
        ]);

        Parceiro::create($validated);

        return redirect()->route('parceiros.index')->with('success', 'Parceiro cadastrado com sucesso.');
    }

    public function edit(Parceiro $parceiro)
    {
        return Inertia::render('Parceiros/Edit', ['item' => $parceiro]);
    }

    public function update(Request $request, Parceiro $parceiro)
    {
        $validated = $request->validate([
            'nome_parceiro' => 'required|string|max:255',
        ]);

        $parceiro->update($validated);

        return redirect()->route('parceiros.index')->with('success', 'Parceiro atualizado com sucesso.');
    }

    public function destroy(Parceiro $parceiro)
    {
        $parceiro->delete();
        return redirect()->back()->with('success', 'Parceiro excluído.');
    }

    public function buscarPorNome(Request $request)
    {
        $nome = $request->input('nome');

        if (!$nome || strlen($nome) < 2) {
            return response()->json(['parceiros' => []]);
        }

        $parceiros = Parceiro::where('nome_parceiro', 'like', "%{$nome}%")
            ->select('id', 'nome_parceiro')
            ->orderBy('nome_parceiro')
            ->limit(10)
            ->get();

        return response()->json(['parceiros' => $parceiros]);
    }
}
