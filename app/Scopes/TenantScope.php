<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = self::currentTenantId();

        if ($tenantId !== null) {
            $builder->where(
                $model->qualifyColumn('tenant_id'),
                $tenantId
            );
        }
    }

    /**
     * Retorna o tenant definido para a requisição atual.
     *
     * Sem um tenant na sessão, o escopo não é aplicado. Isso é necessário
     * nos fluxos que antecedem a criação da sessão, como login e cadastro.
     *
     * @return mixed
     */
    public static function currentTenantId()
    {
        return session('user.tenant.id');
    }
}
