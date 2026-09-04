<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;

class EnsureTenantSubdomain
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Ensure the authenticated user is accessing their own tenant domain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $tenant = $user ? $user->tenant : null;
        $requestedTenant = (string) $request->route('tenant');

        if (!$tenant) {
            abort(403, 'Usuário sem tenant vinculado.');
        }

        if (! hash_equals((string) $tenant->slug, $requestedTenant)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'O domínio informado não pertence ao tenant do usuário.',
                ], 403);
            }

            $path = '/'.$request->path();

            if ($request->getQueryString()) {
                $path .= '?'.$request->getQueryString();
            }

            return redirect()->away(
                $this->userService->tenantUrl($tenant->slug, $path)
            );
        }
        
        $request->route()->forgetParameter('tenant');

        return $next($request);
    }
}
