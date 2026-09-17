<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Role extends Model
{
    use HasFactory;
    use NodeTrait;
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'parent_id',
        'tenant_id',
    ];

    protected function getScopeAttributes(): array
    {
        return ['tenant_id'];
    }

    public function permissions()
    {
        $relation = $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id'
        );

        $tenantId = TenantScope::currentTenantId();

        if ($tenantId !== null) {
            $relation->withPivotValue('tenant_id', $tenantId);
        }

        return $relation;
    }
}
