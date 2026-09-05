<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleAccess
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();
        if (!$user) {
            abort(401);
        }

        $normalizedRole = $this->normalizeRole((string) ($user->role ?? ''));
        $normalizedAllowed = array_map(fn (string $role): string => $this->normalizeAllowedRole($role), $allowedRoles);

        if (!in_array($normalizedRole, $normalizedAllowed, true)) {
            abort(403, 'Acces refuse pour ce profil.');
        }

        return $next($request);
    }

    private function normalizeAllowedRole(string $role): string
    {
        $value = strtolower(trim($role));

        return match ($value) {
            'admin', 'administrateur', 'super_admin' => 'admin',
            'cashier', 'caissier', 'comptoiriste' => 'cashier',
            'cashier1', 'caissier1', 'comptoiriste1', 'caissier_1', 'cashier_1', 'comptoiriste_1' => 'cashier1',
            'cashier2', 'caissier2', 'comptoiriste2', 'caissier_2', 'cashier_2', 'comptoiriste_2' => 'cashier2',
            'serveuse', 'waitress', 'cuisiniere', 'cuisinière' => 'serveuse',
            default => $value,
        };
    }

    private function normalizeRole(string $role): string
    {
        return $this->normalizeAllowedRole($role);
    }
}
