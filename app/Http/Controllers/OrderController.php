<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Customer;
use App\Models\DesignOption;
use App\Models\GarmentType;
use App\Models\Order;
use App\Models\OrderGarment;
use App\Models\OrderMeasurement;
use App\Models\MeasurementPoint;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController
{
    
    public function index(): View
    {
        return view('orders.form', $this->viewData());
    }

    
    public function search(Request $request): View
    {
        $q = trim($request->input('q', ''));

        $order = null;
        if ($q !== '') {
            $order = Order::with([
                'customer',
                'garments.garmentType',
                'garments.measurements.measurementPoint',
                'designOptions',
            ])
            ->where('order_no', $q)
            ->orWhereHas('customer', fn ($qb) => $qb->where('phone', $q))
            ->latest()
            ->first();
        }

        return view('orders.form', $this->viewData($order, $q));
    }

    // ── search-only page
    public function searchOrder(Request $request): View
    {
        $q  = trim($request->input('q', ''));
        $cn = trim($request->input('cn', ''));

        // Customer-number search
        $customerSummary = null;
        if ($cn !== '') {
            $customer = ctype_digit($cn) ? Customer::find((int) $cn) : null;

            if ($customer) {
                $orders = $customer->orders()->latest('id')->get();

                $totalPrice     = (float) $orders->sum('price');
                $totalPaid      = (float) $orders->sum('advance_paid');

                $customerSummary = [
                    'found'           => true,
                    'customer'        => $customer,
                    'orders'          => $orders,
                    'orders_count'    => $orders->count(),
                    'total_price'     => $totalPrice,
                    'total_paid'      => $totalPaid,
                    'total_remaining' => $totalPrice - $totalPaid,
                ];
            } else {
                $customerSummary = ['found' => false, 'cn' => $cn];
            }
        }

        $order = null;
        if ($q !== '') {
            $order = Order::with([
                'customer',
                'garments.garmentType',
                'garments.measurements.measurementPoint',
                'designOptions',
            ])
            ->where('order_no', $q)
            ->orWhereHas('customer', fn ($qb) => $qb->where('phone', $q))
            ->latest()
            ->first();
        }

        return view('orders.search', array_merge(
            $this->viewData($order, $q),
            ['cn' => $cn, 'customerSummary' => $customerSummary]
        ));
    }

    // store new order
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = null;
        DB::transaction(function () use ($request, &$order) {
            $customer = $this->upsertCustomer($request);
            $order    = $this->createOrder($request, $customer);
            $this->syncMeasurements($request, $order);
            $this->syncDesignOptions($request, $order);
            $this->recordPaymentDelta($order, 0, (float) $order->advance_paid);
        });

        return redirect()->route('orders.search', ['q' => $order->order_no])
            ->with('success', 'Order saved successfully.');
    }

    // ── update 
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        DB::transaction(function () use ($request, $order) {
            $previousAdvance = (float) $order->advance_paid;

            $customer = $this->upsertCustomer($request, $order->customer);
            $order->update([
                'customer_id'   => $customer->id,
                'order_no'      => $request->input('order_no', $order->order_no),
                'booking_date'  => $this->parseDate($request->input('booking_date')),
                'delivery_date' => $this->parseDate($request->input('delivery_date')),
                'quantity'      => $request->input('quantity', 1),
                'price'         => $request->input('price', 0),
                'advance_paid'  => $request->input('advance_paid', 0),
                'colour_note'   => $request->input('colour_note'),
                'extra_notes'   => $request->input('extra_notes'),
                'status'        => $request->input('status', $order->status),
            ]);
            $this->syncMeasurements($request, $order);
            $this->syncDesignOptions($request, $order);
            $this->recordPaymentDelta($order, $previousAdvance, (float) $order->advance_paid);
        });

        // If this update was submitted from the dedicated "search & update"
        // page, send the user back there instead of the combined page —
        // keeps that flow completely separate from new-order creation.
        if ($request->input('return_to') === 'lookup') {
            return redirect()->route('orders.searchOrder', ['q' => $order->order_no])
                ->with('success', 'Order updated successfully.');
        }

        return redirect()->route('orders.search', ['q' => $order->order_no])
            ->with('success', 'Order updated successfully.');
    }

    // ── destroy 
    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order deleted.');
    }

    // ── print 

    public function print(Order $order): View
    {
        $order->load([
            'customer',
            'garments.garmentType',
            'garments.measurements.measurementPoint',
            'designOptions',
        ]);

        return view('orders.print', compact('order'));
    }

    // ── Private helpers
    private function viewData(?Order $order = null, string $searchQuery = ''): array
    {
        $garmentTypes  = GarmentType::with('measurementPoints')->orderBy('sort_order')->get();
        $designOptions = DesignOption::orderBy('sort_order')->get()->groupBy('category');

        // Selected design option IDs
        $selectedOptionIds = $order ? $order->selectedOptionIds() : [];

        // If no order, pre-tick defaults
        if (! $order) {
            $selectedOptionIds = DesignOption::where('is_default', true)->pluck('id')->toArray();
        }

        // Build measurements map: [garmentCode => [pointCode => value]]
        // Always initialize all keys so Blade templates never get undefined index errors
        $measurements = [];
        foreach ($garmentTypes as $gt) {
            $measurements[$gt->code] = $order ? $order->measurementsFor($gt->code) : [];
        }

        $nextOrderNo = Order::nextOrderNo();

        return compact('order', 'garmentTypes', 'designOptions', 'selectedOptionIds', 'measurements', 'searchQuery', 'nextOrderNo');
    }

    /**
     * Create or update customer from request data.
     */
    private function upsertCustomer(Request $request, ?Customer $existing = null): Customer
    {
        $data = [
            'name'      => $request->input('name'),
            'phone'     => $request->input('phone'),
            'reference' => $request->input('reference'),
        ];

        if ($existing) {
            $existing->update($data);
            return $existing;
        }

        // Re-use customer if phone matches
        if ($request->filled('phone')) {
            $customer = Customer::findByPhone($request->input('phone'));
            if ($customer) {
                $customer->update($data);
                return $customer;
            }
        }

        return Customer::create($data);
    }

    /**
     * Parse a date to Y-m-d. Handles Y-m-d (from type=date inputs) and dd/mm/yyyy legacy format.
     */
    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        // Legacy dd/mm/yyyy
        if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $date)) {
            $parsed = \DateTime::createFromFormat('d/m/Y', $date);
            return $parsed ? $parsed->format('Y-m-d') : null;
        }
        // Already Y-m-d (from HTML date picker)
        return $date;
    }

  
    private function createOrder(Request $request, Customer $customer): Order
    {
        return Order::create([
            'order_no'      => $request->input('order_no') ?: Order::nextOrderNo(),
            'customer_id'   => $customer->id,
            'booking_date'  => $this->parseDate($request->input('booking_date')),
            'delivery_date' => $this->parseDate($request->input('delivery_date')),
            'quantity'      => $request->input('quantity', 1),
            'price'         => $request->input('price', 0),
            'advance_paid'  => $request->input('advance_paid', 0),
            'colour_note'   => $request->input('colour_note'),
            'extra_notes'   => $request->input('extra_notes'),
            'status'        => $request->input('status', 'pending'),
        ]);
    }

    
    private function recordPaymentDelta(Order $order, float $previousAdvance, float $newAdvance): void
    {
        $delta = round($newAdvance - $previousAdvance, 2);

        if ($delta === 0.0) {
            return;
        }

        try {
            Payment::create([
                'order_id' => $order->id,
                'amount'   => $delta,
                'paid_at'  => Carbon::today(),
                'note'     => $delta > 0 ? 'Advance payment recorded' : 'Advance payment correction',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log payment for order #' . $order->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Sync measurements for both kameez and waistcoat.
     */
    private function syncMeasurements(Request $request, Order $order): void
    {
        $garmentTypes = GarmentType::with('measurementPoints')->get()->keyBy('code');

        foreach (['kameez', 'waistcoat'] as $garmentCode) {
            $values = $request->input($garmentCode, []);
            if (empty($values)) {
                continue;
            }

            $gt = $garmentTypes->get($garmentCode);
            if (! $gt) {
                continue;
            }

            // Get or create the order_garment row
            $og = OrderGarment::firstOrCreate([
                'order_id'        => $order->id,
                'garment_type_id' => $gt->id,
            ], ['quantity' => 1]);

            // Index measurement points by code
            $pointMap = $gt->measurementPoints->keyBy('code');

            foreach ($values as $code => $value) {
                $point = $pointMap->get($code);
                if (! $point) {
                    continue;
                }

                OrderMeasurement::updateOrCreate(
                    [
                        'order_garment_id'    => $og->id,
                        'measurement_point_id' => $point->id,
                    ],
                    ['value' => $value !== '' ? $value : null]
                );
            }
        }
    }

    
    private function syncDesignOptions(Request $request, Order $order): void
    {
        $ids = $request->input('design_options', []);
        $order->designOptions()->sync($ids);
    }
}