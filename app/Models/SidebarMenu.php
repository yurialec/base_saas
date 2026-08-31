<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class SidebarMenu extends Model
{
    use HasFactory;
    use NodeTrait;

    protected $fillable = [
        'title',
        'icon',
        'route',
        'url',
        'is_active',
        'order',
        'alert',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'alert' => 'boolean',
    ];
}
