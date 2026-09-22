<?php

namespace App\Http\Controllers;

use App\Services\AgendaService;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    private $service;

    public function __construct(AgendaService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $validated = $request->validate(['data' => ['nullable', 'date_format:Y-m-d']]);

        return response()->json([
            'data' => $this->service->all($validated['data'] ?? null),
            'timezone' => config('services.google.calendar_timezone'),
            'duration_minutes' => (int) config('services.google.calendar_duration'),
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
}
