<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use App\Services\GoogleCalendarConnectionService;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    private $service;

    public function __construct(AgendaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, GoogleCalendarConnectionService $connection)
    {
        $validated = $request->validate(['data' => ['nullable', 'date_format:Y-m-d']]);

        return response()->json([
            'data' => $this->service->all($validated['data'] ?? null),
            'timezone' => config('services.google.calendar_timezone'),
            'duration_minutes' => (int) config('services.google.calendar_duration'),
            'google_calendar' => $connection->status($request->user()),
            'connection_url' => route('google.calendar.connect', ['tenant' => $request->user()->tenant->slug], false),
            'connection_result' => $request->hasSession() ? $request->session()->pull('google_calendar_result') : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'comentario' => ['required', 'string', 'max:5000'],
        ]);

        return response()->json($this->service->create($validated), 201);
    }

    public function sync($id)
    {
        return response()->json($this->service->sync((int) $id));
    }
}
