<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Station\StoreStationRequest;
use App\Http\Requests\Station\UpdateStationRequest;
use App\Http\Resources\StationResource;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class StationController extends Controller
{
    /**
     * Display a paginated listing of charging stations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Station::query()->with(['connectors', 'photos', 'chargingApp']);

        // Filter: Usable only (native or adapter_ok)
        if ($request->boolean('usable_only')) {
            $query->usableOnly();
        }

        // Filter: Connector current_type (AC | DC)
        if ($request->filled('current_type')) {
            $currentType = strtoupper((string) $request->query('current_type'));
            $query->whereHas('connectors', function (Builder $q) use ($currentType): void {
                $q->where('current_type', $currentType);
            });
        }

        // Filter: Connector plug_type (GBT, TYPE2, CCS2, CHADEMO, OTHER)
        if ($request->filled('plug_type')) {
            $plugType = strtoupper((string) $request->query('plug_type'));
            $query->whereHas('connectors', function (Builder $q) use ($plugType): void {
                $q->where('plug_type', $plugType);
            });
        }

        // Filter: Connector compat_status (native, adapter_ok, adapter_failed, incompatible, untested)
        if ($request->filled('compat_status')) {
            $compatStatus = strtolower((string) $request->query('compat_status'));
            $query->whereHas('connectors', function (Builder $q) use ($compatStatus): void {
                $q->where('compat_status', $compatStatus);
            });
        }

        // Filter: Search keyword q
        if ($request->filled('q')) {
            $searchTerm = '%'.trim((string) $request->query('q')).'%';
            $query->where(function (Builder $q) use ($searchTerm): void {
                $q->where('name', 'ilike', $searchTerm)
                    ->orWhere('operator', 'ilike', $searchTerm)
                    ->orWhere('address', 'ilike', $searchTerm);
            });
        }

        // Filter: updated_since
        if ($request->filled('updated_since')) {
            try {
                $updatedSince = Carbon::parse((string) $request->query('updated_since'));
                $query->where('updated_at', '>=', $updatedSince);
            } catch (\Throwable) {
                // Ignore invalid date
            }
        }

        // Location & Distance sorting (Haversine)
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (is_numeric($lat) && is_numeric($lng)) {
            $latitude = (float) $lat;
            $longitude = (float) $lng;

            $query->withDistance($latitude, $longitude);

            if ($request->filled('radius_km') && is_numeric($request->query('radius_km'))) {
                $radiusKm = (float) $request->query('radius_km');
                $query->withinRadius($latitude, $longitude, $radiusKm);
            }

            $query->orderBy('distance_km', 'asc');
        } else {
            $query->orderByDesc('is_favorite')->orderBy('name', 'asc');
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $stations = $query->paginate($perPage)->withQueryString();

        return StationResource::collection($stations);
    }

    /**
     * Store a newly created charging station.
     */
    public function store(StoreStationRequest $request): JsonResponse
    {
        $station = Station::create($request->validated());
        $station->load(['connectors', 'photos', 'chargingApp']);

        return response()->json([
            'message' => 'Station created successfully.',
            'data' => new StationResource($station),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified charging station.
     */
    public function show(Station $station): StationResource
    {
        $station->load(['connectors', 'photos', 'chargingApp']);

        return new StationResource($station);
    }

    /**
     * Update the specified charging station.
     */
    public function update(UpdateStationRequest $request, Station $station): JsonResponse
    {
        $station->update($request->validated());
        $station->load(['connectors', 'photos', 'chargingApp']);

        return response()->json([
            'message' => 'Station updated successfully.',
            'data' => new StationResource($station),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified charging station (soft delete).
     */
    public function destroy(Station $station): JsonResponse
    {
        $station->delete();

        return response()->json([
            'message' => 'Station deleted successfully.',
        ], Response::HTTP_OK);
    }
}
