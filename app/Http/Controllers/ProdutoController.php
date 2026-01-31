<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Produto;

class ProdutoController extends Controller
{
    public function index()
    {
        $data = Produto::latest()->paginate(15);
        return Inertia::render('Produtos/Index', ['data' => $data]);
    }

    public function create()
    {
        return Inertia::render('Produtos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['nome'=>'required|string','sku'=>'nullable|string','preco'=>'nullable|numeric']);
        Produto::create($validated);
        return redirect()->route('produtos.index')->with('success','Criado com sucesso');
    }

    public function show(Produto $produto)
    {
        return Inertia::render('Produtos/Show', ['item' => $produto]);
    }

    public function edit(Produto $produto)
    {
        return Inertia::render('Produtos/Edit', ['item' => $produto]);
    }

    public function update(Request $request, Produto $produto)
    {
        $validated = $request->validate(['nome'=>'required|string','sku'=>'nullable|string','preco'=>'nullable|numeric']);
        $produto->update($validated);
        return redirect()->route('produtos.index')->with('success','Atualizado');
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->back()->with('success','Excluído');
    }
}
