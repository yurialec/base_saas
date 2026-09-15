<?php

namespace App\Http\Middleware;

use App\Services\AclService;
use Closure;
use Illuminate\Http\Request;

class Acl
{
    protected AclService $aclService;

    public function __construct(AclService $aclService)
    {
        $this->aclService = $aclService;
    }

    public function handle(Request $request, Closure $next, string $permissionNeeded)
    {
        if (!$this->aclService->can($permissionNeeded)) {
            return response()->json([
                'message' => 'Você não tem permissão para acessar essa funcionalidade.',
                'code' => 403,
            ], 403);
        }

        return $next($request);
    }
}
