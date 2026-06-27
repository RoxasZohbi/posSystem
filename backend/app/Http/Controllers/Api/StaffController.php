<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Staff::with('createdBy:id,name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'nic' => 'required|string|max:20',
            'thumbprint' => 'nullable|string',
        ]);
        return response()->json(Staff::create([...$validated, 'created_by' => $request->user()->id]), 201);
    }

    public function update(Request $request, Staff $staff): JsonResponse
    {
        $staff->update([
            ...$request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'sometimes|string|max:20',
                'nic' => 'sometimes|string|max:20',
                'thumbprint' => 'nullable|string',
            ]),
            'updated_by' => $request->user()->id,
        ]);
        return response()->json($staff);
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $staff->delete();
        return response()->json(['message' => 'Staff member deleted.']);
    }
}
