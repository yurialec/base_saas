<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
