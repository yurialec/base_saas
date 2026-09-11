<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'cpf_cnpj',
        'slug',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
