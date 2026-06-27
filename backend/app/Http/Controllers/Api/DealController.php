<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Deal::with(['services', 'createdBy:id,name'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'service_ids' => 'array',
            'service_ids.*' => 'exists:services,id',
        ]);
        $deal = Deal::create(['name' => $validated['name'], 'price' => $validated['price'], 'is_active' => $validated['is_active'] ?? true, 'created_by' => $request->user()->id]);
        if (!empty($validated['service_ids'])) $deal->services()->sync($validated['service_ids']);
        return response()->json($deal->load('services'), 201);
    }

    public function update(Request $request, Deal $deal): JsonResponse
    {
        $validated = $request->validate(['name' => 'sometimes|string|max:255', 'price' => 'sometimes|numeric|min:0', 'is_active' => 'boolean', 'service_ids' => 'array', 'service_ids.*' => 'exists:services,id']);
        $deal->update(array_filter($validated, fn($k) => $k !== 'service_ids', ARRAY_FILTER_USE_KEY));
        if (array_key_exists('service_ids', $validated)) $deal->services()->sync($validated['service_ids']);
        return response()->json($deal->load('services'));
    }

    public function destroy(Deal $deal): JsonResponse
    {
        $deal->delete();
        return response()->json(['message' => 'Deal deleted.']);
    }
}
