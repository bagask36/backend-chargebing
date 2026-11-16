<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChargingSession\StoreChargingSessionRequest;
use App\Http\Requests\ChargingSession\UpdateChargingSessionRequest;
use App\Http\Resources\ChargingSessionResource;
use App\Models\ChargingSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ChargingSessionController extends Controller
{
    /**
     * Display a listing of charging sessions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ChargingSession::query()->with(['station', 'connector']);

        if ($request->filled('charging_mode')) {
            $query->where('charging_mode', strtoupper((string) $request->query('charging_mode')));
        }

        if ($request->filled('station_id') && is_numeric($request->query('station_id'))) {
            $query->where('station_id', (int) $request->query('station_id'));
        }

        if ($request->filled('started_from')) {
            try {
                $query->where('started_at', '>=', Carbon::parse((string) $request->query('started_from'))->startOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        if ($request->filled('started_until')) {
            try {
                $query->where('started_at', '<=', Carbon::parse((string) $request->query('started_until'))->endOfDay());
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $sessions = $query->orderByDesc('started_at')->paginate($perPage)->withQueryString();

        return ChargingSessionResource::collection($sessions);
    }

    /**
     * Store a newly created charging session.
     */
    public function store(StoreChargingSessionRequest $request): JsonResponse
    {
        $session = ChargingSession::create($request->validated());
        $session->load(['station', 'connector']);

        return response()->json([
            'message' => 'Charging session recorded successfully.',
            'data' => new ChargingSessionResource($session),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified charging session.
     */
    public function show(ChargingSession $chargingSession): ChargingSessionResource
    {
        $chargingSession->load(['station', 'connector']);

        return new ChargingSessionResource($chargingSession);
    }

    /**
     * Update the specified charging session.
     */
    public function update(UpdateChargingSessionRequest $request, ChargingSession $chargingSession): JsonResponse
    {
        $chargingSession->update($request->validated());
        $chargingSession->load(['station', 'connector']);

        return response()->json([
            'message' => 'Charging session updated successfully.',
            'data' => new ChargingSessionResource($chargingSession),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified charging session.
     */
    public function destroy(ChargingSession $chargingSession): JsonResponse
    {
        $chargingSession->delete();

        return response()->json([
            'message' => 'Charging session deleted successfully.',
        ], Response::HTTP_OK);
    }
}
