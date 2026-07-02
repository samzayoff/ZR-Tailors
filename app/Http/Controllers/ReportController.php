<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController
{
    
    private const STATUSES = ['pending', 'stitching', 'delivered', 'returned', 'cancelled'];

    
    private const PAYMENT_STATUSES = ['paid', 'unpaid', 'partial'];

    public function index(Request $request): View
    {
        // ── Date range (defaults to the current month) ──────────────────
        $dateField = $request->input('date_field', 'booking_date');
        if (! in_array($dateField, ['booking_date', 'delivery_date'], true)) {
            $dateField = 'booking_date';
        }

        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->input('date_from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->input('date_to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        // ── Status filters ───────────────────────────────────────────────
        $selectedStatuses = array_values(array_intersect(
            (array) $request->input('status', []),
            self::STATUSES
        ));

        // ── Payment status filters ─────────────────────────────────────
        $selectedPayment = array_values(array_intersect(
            (array) $request->input('payment_status', []),
            self::PAYMENT_STATUSES
        ));

        // ── Search by suit #, customer name, or phone ────────────────────
        $search = trim((string) $request->input('q', ''));

        // ── Build query ───────────────────────────────────────────────
        $query = Order::with('customer');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($qc) use ($search) {
                      $qc->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        } else {
            $query->whereBetween($dateField, [$dateFrom, $dateTo]);
        }

        if (! empty($selectedStatuses)) {
            $query->whereIn('status', $selectedStatuses);
        }

        if (! empty($selectedPayment)) {
            $query->where(function ($q) use ($selectedPayment) {
                foreach ($selectedPayment as $p) {
                    $q->orWhere(function ($qq) use ($p) {
                        match ($p) {
                            'paid'    => $qq->whereColumn('advance_paid', '>=', 'price')->where('price', '>', 0),
                            'unpaid'  => $qq->where('advance_paid', '<=', 0),
                            'partial' => $qq->where('advance_paid', '>', 0)
                                             ->whereColumn('advance_paid', '<', 'price'),
                            default   => null,
                        };
                    });
                }
            });
        }

        $orders = $query->orderByDesc('booking_date')->get();

        // ── Summary numbers ──────────────────────────────────────────────
        $summary = [
            'count'     => $orders->count(),
            'total'     => $orders->sum('price'),
            'collected' => $orders->sum('advance_paid'),
            'due'       => $orders->sum(fn ($o) => max(0, $o->price - $o->advance_paid)),
        ];

        return view('reports.index', [
            'orders'           => $orders,
            'summary'          => $summary,
            'dateField'        => $dateField,
            'dateFrom'         => $dateFrom->toDateString(),
            'dateTo'           => $dateTo->toDateString(),
            'selectedStatuses' => $selectedStatuses,
            'selectedPayment'  => $selectedPayment,
            'statuses'         => self::STATUSES,
            'paymentStatuses'  => self::PAYMENT_STATUSES,
            'search'           => $search,
        ]);
    }

    /**
     * Compute a simple payment status label for a given order.
     */
    public static function paymentStatusFor(Order $order): string
    {
        $price = (float) $order->price;
        $paid  = (float) $order->advance_paid;

        if ($price > 0 && $paid >= $price) {
            return 'paid';
        }
        if ($paid <= 0) {
            return 'unpaid';
        }
        return 'partial';
    }
}