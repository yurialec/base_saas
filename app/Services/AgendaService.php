<?php

namespace App\Services;

use App\Models\Agenda;
use App\Repositories\AgendaRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class AgendaService
{
    private $repository;
    private $calendar;

    public function __construct(AgendaRepositoryInterface $repository, GoogleCalendarService $calendar)
    {
        $this->repository = $repository;
        $this->calendar = $calendar;
    }

    public function all(?string $date = null)
    {
        return $date ? $this->repository->findByDate($date) : $this->repository->all();
    }

    public function create(array $data): array
    {
        // O registro local permanece salvo mesmo se o servico externo falhar.
        $agenda = $this->repository->create($data);

        return $this->synchronize($agenda);
    }

    public function sync(int $id): array
    {
        return $this->synchronize($this->repository->find($id));
    }

    private function synchronize(Agenda $agenda): array
    {
        if ($agenda->google_event_id) {
            return ['data' => $agenda, 'integration' => ['status' => 'synced', 'message' => 'Este agendamento já está sincronizado.']];
        }

        try {
            $eventId = $this->calendar->createEvent($agenda);
            $agenda = $this->repository->saveGoogleEventId($agenda, $eventId);
            $integration = ['status' => 'synced', 'message' => 'Agendamento salvo e sincronizado com o Google Calendar.'];
        } catch (Throwable $exception) {
            // Nao registra tokens, comentario ou resposta completa do provedor.
            Log::warning('Falha na integracao da agenda com Google Calendar.', [
                'agenda_id' => $agenda->id,
                'exception' => get_class($exception),
                'code' => $exception->getCode(),
            ]);
            $integration = [
                'status' => 'pending',
                'message' => 'Agendamento salvo localmente, mas a sincronização não foi confirmada. Verifique a autorização do Google Calendar. Não cadastre novamente este horário para tentar sincronizar.',
            ];
        }

        return ['data' => $agenda, 'integration' => $integration];
    }
}
