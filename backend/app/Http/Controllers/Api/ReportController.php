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

        $bills = Bill::whereDate('created_at', $date)
            ->where('status', '!=', 'voided')
            ->with(['items', 'staff:id,name,commission_rate'])
            ->get();

        $expenses = Expense::whereDate('date', $date)->get();

        $totalRevenue = (float) $bills->sum('total');
        $totalExpenses = (float) $expenses->sum('amount');

        $staffSummary = $bills->groupBy('staff_id')->map(function ($staffBills, $staffId) {
            $staff = $staffBills->first()->staff;
            $staffTotal = (float) $staffBills->sum('total');
            $commissionRate = (float) ($staff->commission_rate ?? 0);
            $commission = round($staffTotal * $commissionRate / 100, 2);

            return [
                'staff_id' => $staffId,
                'staff_name' => $staff->name ?? 'Unknown',
                'commission_rate' => $commissionRate,
                'bill_count' => $staffBills->count(),
                'total' => $staffTotal,
                'commission' => $commission,
            ];
        })->values();

        $totalCommissions = (float) $staffSummary->sum('commission');
        $net = $totalRevenue - $totalCommissions - $totalExpenses;

        $paymentBreakdown = [
            'cash' => (float) $bills->where('payment_type', 'cash')->sum('total'),
            'card' => (float) $bills->where('payment_type', 'card')->sum('total'),
            'online' => (float) $bills->where('payment_type', 'online')->sum('total'),
        ];

        return response()->json([
            'date' => $date,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_commissions' => $totalCommissions,
            'net' => $net,
            'bill_count' => $bills->count(),
            'payment_breakdown' => $paymentBreakdown,
            'staff_summary' => $staffSummary,
            'bills' => $bills,
            'expenses' => $expenses,
        ]);
    }
}
