<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Connector\StoreConnectorRequest;
use App\Http\Requests\Connector\UpdateConnectorRequest;
use App\Http\Resources\ConnectorResource;
use App\Models\Connector;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class StationConnectorController extends Controller
{
    /**
     * List all connectors for a station.
     */
    public function index(Station $station): AnonymousResourceCollection
    {
        return ConnectorResource::collection($station->connectors()->orderBy('current_type')->get());
    }

    /**
     * Store a newly created connector for a station.
     */
    public function store(StoreConnectorRequest $request, Station $station): JsonResponse
    {
        $connector = $station->connectors()->create($request->validated());

        return response()->json([
            'message' => 'Connector added successfully.',
            'data' => new ConnectorResource($connector),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified connector of a station.
     */
    public function show(Station $station, Connector $connector): ConnectorResource
    {
        abort_if($connector->station_id !== $station->id, Response::HTTP_NOT_FOUND, 'Connector not found on this station.');

        return new ConnectorResource($connector);
    }

    /**
     * Update the specified connector of a station.
     */
    public function update(UpdateConnectorRequest $request, Station $station, Connector $connector): JsonResponse
    {
        abort_if($connector->station_id !== $station->id, Response::HTTP_NOT_FOUND, 'Connector not found on this station.');

        $connector->update($request->validated());

        return response()->json([
            'message' => 'Connector updated successfully.',
            'data' => new ConnectorResource($connector),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified connector from a station.
     */
    public function destroy(Station $station, Connector $connector): JsonResponse
    {
        abort_if($connector->station_id !== $station->id, Response::HTTP_NOT_FOUND, 'Connector not found on this station.');

        $connector->delete();

        return response()->json([
            'message' => 'Connector deleted successfully.',
        ], Response::HTTP_OK);
    }
}
