<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\EvModel\StoreEvModelRequest;
use App\Http\Requests\EvModel\UpdateEvModelRequest;
use App\Http\Resources\EvModelResource;
use App\Models\EvModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class EvModelController extends Controller
{
    /**
     * Display a listing of EV models with search and filtering.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = EvModel::query()
            ->search($request->input('q'))
            ->brand($request->input('brand'));

        if ($request->has('active_only') && filter_var($request->input('active_only'), FILTER_VALIDATE_BOOLEAN)) {
            $query->active();
        }

        if ($request->has('has_dc')) {
            $hasDc = filter_var($request->input('has_dc'), FILTER_VALIDATE_BOOLEAN);
            if ($hasDc) {
                $query->whereNotNull('dc_plug_type');
            } else {
                $query->whereNull('dc_plug_type');
            }
        }

        if ($plug = $request->input('plug_type')) {
            $query->where(function ($q) use ($plug) {
                $q->where('ac_plug_type', $plug)
                    ->orWhere('dc_plug_type', $plug);
            });
        }

        $query->orderBy('brand')->orderBy('model_name')->orderBy('variant');

        // Allow unpaginated list if mobile app requests dropdown options (?all=1)
        if ($request->boolean('all')) {
            return EvModelResource::collection($query->get());
        }

        $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

        return EvModelResource::collection($query->paginate($perPage));
    }

    /**
     * Store a newly created EV model.
     */
    public function store(StoreEvModelRequest $request): JsonResponse
    {
        $evModel = EvModel::create($request->validated());

        return response()->json([
            'message' => 'EV model created successfully.',
            'data' => new EvModelResource($evModel),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified EV model.
     */
    public function show(EvModel $evModel): EvModelResource
    {
        return new EvModelResource($evModel);
    }

    /**
     * Update the specified EV model.
     */
    public function update(UpdateEvModelRequest $request, EvModel $evModel): JsonResponse
    {
        $evModel->update($request->validated());

        return response()->json([
            'message' => 'EV model updated successfully.',
            'data' => new EvModelResource($evModel),
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified EV model (soft delete).
     */
    public function destroy(EvModel $evModel): JsonResponse
    {
        $evModel->delete();

        return response()->json([
            'message' => 'EV model deleted successfully.',
        ], Response::HTTP_OK);
    }
}
