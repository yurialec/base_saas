<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'role_permission';

    protected $fillable = [
        'role_id',
        'permission_id',
        'tenant_id',
    ];
}
