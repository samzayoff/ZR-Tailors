<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController
{
    //  View every current customer
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));

        $query = Customer::withCount('orders')
            ->withSum('orders as total_price', 'price')
            ->withSum('orders as total_paid', 'advance_paid');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                //   ->orWhere('phone', 'like', "%{$search}%");

                if (ctype_digit($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        $customers = $query->orderBy('id')->paginate(25)->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'search'    => $search,
        ]);
    }

    //  Lookup a customer by ID (for AJAX)
    public function lookup(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (! $customer) {
            return response()->json([
                'found' => false,
                'message' => 'No customer found with number #' . $id,
            ], 404);
        }

        $garments = [];
        $lastOrder = null;

        try {
            $lastOrder = $customer->orders()
                ->with(['garments.garmentType', 'garments.measurements.measurementPoint'])
                ->latest('id')
                ->first();

            if ($lastOrder) {
                foreach ($lastOrder->garments as $garment) {
                    $points = [];
                    foreach ($garment->measurements as $m) {
                        if ($m->value === null || $m->value === '') {
                            continue;
                        }
                        //  if the measurement point was deleted/renamed
                        $label = $m->measurementPoint?->name_en ?? $m->measurementPoint?->code;
                        $code  = $m->measurementPoint?->code;
                        if ($label === null || $code === null) {
                            continue;
                        }
                        $points[] = [
                            'code'  => $code,
                            'label' => $label,
                            'value' => $m->value,
                        ];
                    }
                    if (! empty($points)) {
                        $garments[] = [
                            'garment_code' => $garment->garmentType?->code,
                            'garment'      => $garment->garmentType?->name_en ?? $garment->garmentType?->code ?? 'Garment',
                            'points'       => $points,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
           $garments = [];
        }

        return response()->json([
            'found'    => true,
            'id'       => $customer->id,
            'name'     => $customer->name,
            'phone'    => $customer->phone,
            'reference'=> $customer->reference,
            'last_order_no' => $lastOrder?->order_no ?? null,
            'garments' => $garments,
        ]);
    }
}