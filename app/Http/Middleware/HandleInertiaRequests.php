<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = [];
        if ($user) {
            $user->load('empresa');
            try {
                // Admin tem todas as permissões; senão usa Spatie
                if ($user->is_admin) {
                    $permissions = \Database\Seeders\PermissionSeeder::PERMISSIONS;
                } else {
                    $permissions = $user->getAllPermissions()->pluck('name')->toArray();
                }
            } catch (\Throwable $e) {
                \Log::warning('Erro ao carregar permissões: ' . $e->getMessage());
                $permissions = [];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'permissions' => $permissions,
            ],
        ];
    }
}
