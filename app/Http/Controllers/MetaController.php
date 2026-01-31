<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Meta;

class MetaController extends Controller
{
    public function index()
    {
        $data = Meta::orderByDesc('ano')->orderByDesc('mes')->paginate(12);
        return Inertia::render('Metas/Index', ['data' => $data]);
    }

    public function create()
    { return Inertia::render('Metas/Create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'ano' => 'required|integer|min:2000|max:2100',
            'meta_faturamento' => 'nullable|numeric',
            'meta_producao_varejo' => 'nullable|integer',
            'meta_producao_atacado' => 'nullable|integer',
        ]);
        Meta::create($validated);
        return redirect()->route('metas.index')->with('success','Meta salva');
    }
}
