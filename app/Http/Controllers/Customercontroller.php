<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController
{
    /**
     * Look up a customer by their customer number (ID) and return their
     * name plus the measurements from their most recent order.
     *
     * This is read-only / for reference only — it is used to display an
     * existing customer's last measurements while filling out a NEW order,
     * without touching (and therefore without affecting) the measurement
     * fields of the order currently being created or edited.
     */
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
                        // Skip gracefully if the measurement point was deleted/renamed
                        // instead of throwing on a null relation.
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
            // Never let a malformed/orphaned measurement row break the lookup —
            // the customer's core details below are still valid and useful.
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