<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChargingApp\StoreChargingAppRequest;
use App\Http\Requests\ChargingApp\UpdateChargingAppRequest;
use App\Http\Resources\ChargingAppResource;
use App\Models\ChargingApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ChargingAppController extends Controller
{
    /**
     * Display a listing of charging apps.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = ChargingApp::query()
            ->search($request->input('q'));

        if ($request->has('active_only') && filter_var($request->input('active_only'), FILTER_VALIDATE_BOOLEAN)) {
            $query->active();
        }

        $query->orderBy('name');

        if ($request->boolean('all')) {
            return ChargingAppResource::collection($query->get());
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        return ChargingAppResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created charging app.
     */
    public function store(StoreChargingAppRequest $request): JsonResponse
    {
        $chargingApp = ChargingApp::create($request->validated());

        return response()->json([
            'message' => 'Charging app created successfully.',
            'data' => new ChargingAppResource($chargingApp),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified charging app.
     */
    public function show(ChargingApp $chargingApp): ChargingAppResource
    {
        return new ChargingAppResource($chargingApp);
    }

    /**
     * Update the specified charging app.
     */
    public function update(UpdateChargingAppRequest $request, ChargingApp $chargingApp): JsonResponse
    {
        $chargingApp->update($request->validated());

        return response()->json([
            'message' => 'Charging app updated successfully.',
            'data' => new ChargingAppResource($chargingApp),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified charging app (soft delete).
     */
    public function destroy(ChargingApp $chargingApp): JsonResponse
    {
        $chargingApp->delete();

        return response()->json([
            'message' => 'Charging app deleted successfully.',
        ], Response::HTTP_OK);
    }
}

