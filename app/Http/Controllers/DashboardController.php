<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController
{
    // Cancelled and returned suits are not completed sales — their price
    // is excluded from every sales/amount total across the app.
    private const EXCLUDED_SALE_STATUSES = ['cancelled', 'returned'];

    public function index(): View
    {
        $today = Carbon::today();

        // ── Orders booked today (drives "Orders Booked Today") ───────────
        $bookedToday = Order::whereDate('booking_date', $today)->get();

        // ── Actual cash received today (from the payments ledger) ────────
        $collectedToday = Payment::whereDate('paid_at', $today)
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', self::EXCLUDED_SALE_STATUSES);
            })
            ->sum('amount');

        // ── Orders due for delivery today ────────────────────────────────
        $dueToday = Order::with('customer')
            ->whereDate('delivery_date', $today)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->orderBy('created_at')
            ->get();

        // ── All pending orders
        $pendingOrders = Order::with('customer')
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->orderBy('delivery_date')
            ->paginate(12, ['*'], 'page')
            ->withQueryString();

        $pendingOrders->getCollection()->transform(function ($order) use ($today) {
            $deliveryDate = $order->delivery_date;
            if ($deliveryDate && $deliveryDate->lt($today)) {
                $order->due_state = 'overdue';
            } elseif ($deliveryDate && $deliveryDate->isSameDay($today)) {
                $order->due_state = 'today';
            } else {
                $order->due_state = 'upcoming';
            }
            return $order;
        });

        // ── Outstanding balance
        $outstandingBalance = Order::whereNotIn('status', self::EXCLUDED_SALE_STATUSES)
            ->get()
            ->sum(fn($o) => max(0, $o->price - $o->advance_paid));

        // ── Total customers
        $totalCustomers = Customer::count();

        // ── Last 30 days (rolling window ending today) ────────────────────
        $rangeStart = $today->copy()->subDays(29);

        $ordersInRange = Order::whereBetween('booking_date', [$rangeStart, $today])
            ->get()
            ->groupBy(fn($o) => Carbon::parse($o->booking_date)->format('Y-m-d'));

        // Pulled straight from the payments ledger (
        $paymentsInRange = Payment::whereBetween('paid_at', [$rangeStart, $today])
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', self::EXCLUDED_SALE_STATUSES);
            })
            ->get()
            ->groupBy(fn($p) => $p->paid_at->format('Y-m-d'));

        $chartLabels = [];
        $chartOrders = [];
        $chartCollected = [];

        for ($d = $rangeStart->copy(); $d->lte($today); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $chartLabels[] = $d->format('d M');
            $chartOrders[] = $ordersInRange->get($key, collect())->count();
            $chartCollected[] = $paymentsInRange->get($key, collect())->sum('amount');
        }

        // ── Total orders 
        $totalOrders = Order::count();

        // ── Total sales
        $totalSales = Order::whereNotIn('status', self::EXCLUDED_SALE_STATUSES)->sum('price');

        // ── Total due orders 
        $totalDueOrders = Order::whereDate('delivery_date', '<', $today)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        return view('dashboard.index', [
            'todaySales' => $bookedToday->whereNotIn('status', self::EXCLUDED_SALE_STATUSES)->sum('price'),
            'todayCollected' => $collectedToday,
            'ordersToday' => $bookedToday->count(),
            'dueTodayCount' => $dueToday->count(),
            'dueBalance' => $outstandingBalance,
            'totalCustomers' => $totalCustomers,
            'dueOrders' => $dueToday,
            'pendingOrders' => $pendingOrders,
            'today' => $today,
            'chartLabels' => $chartLabels,
            'chartOrders' => $chartOrders,
            'chartCollected' => $chartCollected,
            'totalOrders' => $totalOrders,
            'totalSales' => $totalSales,
            'totalDueOrders' => $totalDueOrders,
        ]);
    }
}