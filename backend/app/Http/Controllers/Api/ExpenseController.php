<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Expense::with('loggedBy:id,name')->when($request->date, fn($q) => $q->whereDate('date', $request->date))->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(['date' => 'required|date', 'amount' => 'required|numeric|min:0', 'note' => 'nullable|string']);
        return response()->json(Expense::create([...$validated, 'logged_by' => $request->user()->id]), 201);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted.']);
    }
}
