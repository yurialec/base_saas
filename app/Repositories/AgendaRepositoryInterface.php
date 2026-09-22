<?php

namespace App\Repositories;

use App\Models\Agenda;
use Illuminate\Database\Eloquent\Collection;

interface AgendaRepositoryInterface
{
    public function all(): Collection;
    public function findByDate(string $date): Collection;
    public function create(array $data): Agenda;
    public function saveGoogleEventId(Agenda $agenda, string $eventId): Agenda;
}
