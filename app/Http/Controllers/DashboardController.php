<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController
{
    public function index(): View
    {
        $today = Carbon::today();

        $bookedToday = Order::whereDate('booking_date', $today)->get();

        $collectedToday = Payment::whereDate('paid_at', $today)->sum('amount');

        // ── Orders due for delivery today ────────────────────────────────
        $dueToday = Order::with('customer')
            ->whereDate('delivery_date', $today)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->orderBy('created_at')
            ->get();

        // ── Outstanding balance across ALL customers (all-time) ──────────
        $outstandingBalance = Order::whereNotIn('status', ['cancelled'])
            ->get()
            ->sum(fn ($o) => max(0, $o->price - $o->advance_paid));

        // ── Total customers (all-time) ───────────────────────────────────
        $totalCustomers = Customer::count();

        // ── Last 30 days (rolling window ending today) ────────────────────
        $rangeStart = $today->copy()->subDays(29);

        $ordersInRange = Order::whereBetween('booking_date', [$rangeStart, $today])
            ->get()
            ->groupBy(fn ($o) => $o->booking_date->format('Y-m-d'));

        // Pulled straight from the payments ledger (paid_at = the real day
        // the money was recorded), not guessed from booking_date.
        $paymentsInRange = Payment::whereBetween('paid_at', [$rangeStart, $today])
            ->get()
            ->groupBy(fn ($p) => $p->paid_at->format('Y-m-d'));

        $chartLabels = [];
        $chartOrders = [];
        $chartCollected = [];

        for ($d = $rangeStart->copy(); $d->lte($today); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $chartLabels[]    = $d->format('d M');
            $chartOrders[]    = $ordersInRange->get($key, collect())->count();
            $chartCollected[] = $paymentsInRange->get($key, collect())->sum('amount');
        }

        return view('dashboard.index', [
            'todaySales'      => $bookedToday->sum('price'),
            'todayCollected'  => $collectedToday,
            'ordersToday'     => $bookedToday->count(),
            'dueTodayCount'   => $dueToday->count(),
            'dueBalance'      => $outstandingBalance,
            'totalCustomers'  => $totalCustomers,
            'dueOrders'       => $dueToday,
            'today'           => $today,
            'chartLabels'     => $chartLabels,
            'chartOrders'     => $chartOrders,
            'chartCollected'  => $chartCollected,
        ]);
    }
}