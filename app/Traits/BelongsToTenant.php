<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model): void {
            $tenantId = TenantScope::currentTenantId();

            if ($tenantId !== null) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });

        static::updating(function (Model $model): void {
            $tenantId = TenantScope::currentTenantId();

            if ($tenantId === null) {
                return;
            }

            if ((string) $model->getOriginal('tenant_id') !== (string) $tenantId) {
                throw new LogicException('O registro não pertence ao tenant atual.');
            }

            // Impede que mass assignment mova um registro para outro tenant.
            $model->setAttribute('tenant_id', $tenantId);
        });
    }
}
