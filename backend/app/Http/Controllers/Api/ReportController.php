<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function dailySummary(Request $request): JsonResponse
    {
        $date = $request->get('date', now()->toDateString());
        $bills = Bill::whereDate('created_at', $date)->where('status', '!=', 'voided')->with(['items', 'staff:id,name'])->get();
        $expenses = Expense::whereDate('date', $date)->get();
        $totalRevenue = $bills->sum('total');
        $totalExpenses = $expenses->sum('amount');
        $staffSummary = $bills->groupBy('staff_id')->map(fn($b, $id) => ['staff_id' => $id, 'staff_name' => $b->first()->staff->name ?? 'Unknown', 'bill_count' => $b->count(), 'total' => $b->sum('total')])->values();
        return response()->json(['date' => $date, 'total_revenue' => $totalRevenue, 'total_expenses' => $totalExpenses, 'net' => $totalRevenue - $totalExpenses, 'bill_count' => $bills->count(), 'staff_summary' => $staffSummary, 'bills' => $bills, 'expenses' => $expenses]);
    }
}
