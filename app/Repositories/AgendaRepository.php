<?php

namespace App\Repositories;

use App\Models\Agenda;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AgendaRepository implements AgendaRepositoryInterface
{
    private function currentUser()
    {
        $user = Auth::user();
        abort_unless($user && $user->tenant_id, 403);
        $sessionTenant = TenantScope::currentTenantId();
        abort_if($sessionTenant !== null && (string) $sessionTenant !== (string) $user->tenant_id, 403);

        return $user;
    }

    private function query()
    {
        $user = $this->currentUser();

        // O filtro explicito protege tambem requisicoes sem contexto de sessao.
        return Agenda::query()->where('tenant_id', $user->tenant_id)
            ->where('user_id', $user->id)->orderBy('data')->orderBy('hora')->orderBy('id');
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function findByDate(string $date): Collection
    {
        return $this->query()->whereDate('data', $date)->get();
    }

    public function create(array $data): Agenda
    {
        $user = $this->currentUser();

        return Agenda::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'data' => $data['data'],
            'hora' => $data['hora'],
            'comentario' => $data['comentario'],
        ]);
    }

    public function saveGoogleEventId(Agenda $agenda, string $eventId): Agenda
    {
        $record = $this->query()->findOrFail($agenda->id);
        $record->update(['google_event_id' => $eventId]);

        return $record;
    }
}
