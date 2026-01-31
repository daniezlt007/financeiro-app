<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    /**
     * Lista todos os logs de auditoria
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Filtro por modelo/entidade
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', "%{$request->model_type}%");
        }

        // Filtro por ação
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filtro por usuário
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por ID do registro
        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        // Filtro por período
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        // Busca global
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('model_type', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
                
                // Se a busca for numérica, busca também por model_id
                if (is_numeric($search)) {
                    $q->orWhere('model_id', $search);
                }
            });
        }

        // Ordenação: mais recentes primeiro
        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        // Lista de usuários para o filtro
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('AuditLogs/Index', [
            'data' => $logs,
            'users' => $users,
            'filters' => $request->only(['model_type', 'action', 'user_id', 'model_id', 'data_inicio', 'data_fim', 'search'])
        ]);
    }

    /**
     * Exibe detalhes de um log específico
     */
    public function show($id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return Inertia::render('AuditLogs/Show', [
            'log' => $log
        ]);
    }
}
