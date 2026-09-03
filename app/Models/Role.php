<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Role extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'parent_id',
        'tenant_id',
    ];

    /**
     * Mantém uma árvore Nested Set independente para cada tenant.
     */
    protected function getScopeAttributes(): array
    {
        return ['tenant_id'];
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id'
        );
    }
}
