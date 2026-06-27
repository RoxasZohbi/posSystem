<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|min:3']);
        $customer = Customer::where('phone', 'like', $request->phone . '%')->first();
        return response()->json($customer);
    }
}
