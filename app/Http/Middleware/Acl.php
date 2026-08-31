<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Acl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $permissionNeeded)
    {
        if (!$this->hasPermission($permissionNeeded)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar essa funcionalidade.',
                'code' => 'PERMISSION_DENIED',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    private function hasPermission(string $permissionNeeded): bool
    {
        $permissions = session('user.role.permissions', []);
        return collect($permissions)
            ->contains(
                fn($permission) =>
                isset($permission['slug']) &&
                    $permission['slug'] === $permissionNeeded
            );
    }
}
