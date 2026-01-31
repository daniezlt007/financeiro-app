<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Inertia\Inertia;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index(Request $request)
    {
        $q = Empresa::query();

        if ($s = $request->get('s')) {
            $q->where(function ($w) use ($s) {
                $w->where('nome_fantasia', 'like', "%{$s}%")
                  ->orWhere('razao_social', 'like', "%{$s}%")
                  ->orWhere('cnpj', 'like', "%{$s}%");
            });
        }

        $empresas = $q->latest()->paginate(10)->withQueryString();

        return Inertia::render('Empresas/Index', [
            'empresas' => $empresas,
            'filters'  => ['s' => $s],
        ]);
    }

    public function create()
    {
        return Inertia::render('Empresas/Create');
    }

    public function store(StoreEmpresaRequest $request)
    {
        Empresa::create($request->validated());

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa criada com sucesso!');
    }

    public function show(Empresa $empresa)
    {
        return Inertia::render('Empresas/Show', ['empresa' => $empresa]);
    }

    public function edit(Empresa $empresa)
    {
        return Inertia::render('Empresas/Edit', ['empresa' => $empresa]);
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa)
    {
        $empresa->update($request->validated());

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')
            ->with('success', 'Empresa excluída!');
    }
}
