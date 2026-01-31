<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     * Suporta: admin, funcionario, empresa (legado) ou permissões Spatie (ex: vendas.editar)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$permission) {
            return $next($request);
        }

        // Permissões legadas (admin, funcionario, empresa)
        switch ($permission) {
            case 'admin':
                if (!$user->is_admin) {
                    abort(403, 'Acesso negado. Você não tem permissão de administrador.');
                }
                return $next($request);

            case 'empresa':
                if ($user->is_admin || !$user->empresa_id) {
                    abort(403, 'Acesso negado. Você precisa estar associado a uma empresa.');
                }
                return $next($request);

            case 'funcionario':
                if ($user->is_admin) {
                    abort(403, 'Acesso negado. Esta área é apenas para funcionários.');
                }
                return $next($request);
        }

        // Permissões granulares Spatie: admin tem todas, senão verifica
        if ($user->is_admin) {
            return $next($request);
        }

        $permissions = explode('|', $permission);
        if (!$user->canAny($permissions)) {
            abort(403, 'Acesso negado. Você não tem permissão para esta ação.');
        }

        return $next($request);
    }
}