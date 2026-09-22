<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use BelongsToTenant;

    protected $table = 'agendas';

    protected $fillable = ['tenant_id', 'user_id', 'data', 'hora', 'comentario', 'google_event_id'];

    protected $casts = ['data' => 'date:Y-m-d'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
