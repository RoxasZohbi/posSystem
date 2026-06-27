<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Bill::with(['items', 'staff:id,name', 'billedBy:id,name'])->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'staff_id' => 'required|exists:staff,id',
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:service,deal',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.deal_id' => 'nullable|exists:deals,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
        ]);
        $bill = DB::transaction(function () use ($validated, $request) {
            $bill = Bill::create(['uuid' => Str::uuid(), 'customer_name' => $validated['customer_name'], 'customer_phone' => $validated['customer_phone'], 'staff_id' => $validated['staff_id'], 'billed_by' => $request->user()->id, 'total' => array_sum(array_column($validated['items'], 'price')), 'status' => 'synced', 'synced_at' => now()]);
            foreach ($validated['items'] as $item) $bill->items()->create($item);
            return $bill;
        });
        return response()->json($bill->load(['items', 'staff:id,name']), 201);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate(['bills' => 'required|array', 'bills.*.uuid' => 'required|uuid', 'bills.*.customer_name' => 'required|string', 'bills.*.customer_phone' => 'required|string', 'bills.*.staff_id' => 'required|exists:staff,id', 'bills.*.items' => 'required|array|min:1', 'bills.*.items.*.type' => 'required|in:service,deal', 'bills.*.items.*.name' => 'required|string', 'bills.*.items.*.price' => 'required|numeric|min:0']);
        $synced = [];
        DB::transaction(function () use ($request, &$synced) {
            foreach ($request->bills as $billData) {
                if (Bill::where('uuid', $billData['uuid'])->exists()) { $synced[] = $billData['uuid']; continue; }
                $bill = Bill::create(['uuid' => $billData['uuid'], 'customer_name' => $billData['customer_name'], 'customer_phone' => $billData['customer_phone'], 'staff_id' => $billData['staff_id'], 'billed_by' => $request->user()->id, 'total' => array_sum(array_column($billData['items'], 'price')), 'status' => 'synced', 'synced_at' => now()]);
                foreach ($billData['items'] as $item) $bill->items()->create($item);
                $synced[] = $billData['uuid'];
            }
        });
        return response()->json(['synced' => $synced]);
    }
}
