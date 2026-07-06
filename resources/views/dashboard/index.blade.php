@extends('layouts.app')

@section('content')
    <main>

        <center>
            <h1 class="report-title">Dashboard(Date Today: {{ $today->format('d M Y') }}) </h1>
            {{-- </center>
        <h1 >Date Today: {{ $today->format('d M Y') }}</h1> --}}

            {{-- TODAY'S STAT CARDS --}}
            <div class="report-summary dash-summary dash-summary-6">

                <div class="rs-card">
                    <span class="rs-k">Today's Orders</span>
                    <span class="rs-v num">{{ $ordersToday }}</span>
                </div>
                <div class="rs-card">
                    <span class="rs-k">Due Today</span>
                    <span class="rs-v num">{{ $dueTodayCount }}</span>
                </div>
                <div class="rs-card">
                    <span class="rs-k">Total Due Orders</span>
                    <span class="rs-v num">{{ $totalDueOrders }}</span>
                </div>
                <div class="rs-card">
                    <span class="rs-k">Today's Sales</span>
                    <span class="rs-v num">Rs {{ number_format($todaySales, 0) }}</span>
                </div>
                <div class="rs-card">
                    <span class="rs-k">Total Sales Last Month</span>
                    <span class="rs-v num">Rs {{ number_format($totalSales, 0) }}</span>
                </div>
                <div class="rs-card">
                    <span class="rs-k">Total Due Amount</span>
                    <span class="rs-v num">Rs {{ number_format($dueBalance, 0) }}</span>
                </div>

            </div>
            {{-- MAIN BODY GRID: Orders (left) | Charts (right) --}}
            <div class="dash-grid">

                {{-- LEFT: Orders worklist --}}
                <div class="dash-grid-orders">

                    <div class="report-table-wrap">
                        <table class="report-table order-worklist">
                            <thead>
                                <tr>
                                    <th>Suit #</th>
                                    <th>Customer</th>
                                    {{-- <th>Phone</th> --}}
                                    <th>Due Date</th>
                                    {{-- <th>Balance</th> --}}
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingOrders as $order)
                                    <tr class="due-row due-row--{{ $order->due_state }}"
                                        onclick="window.location='{{ route('orders.index', ['q' => $order->order_no]) }}'"
                                        style="cursor:pointer;">
                                        <td class="num">{{ $order->order_no }}</td>
                                        <td class="cust-name">{{ $order->customer->name ?? '—' }}</td>
                                        {{-- <td class="num">{{ $order->customer->phone ?? '—' }}</td> --}}
                                        <td class="num">
                                            <span class="due-badge due-badge--{{ $order->due_state }}">
                                                @if ($order->due_state === 'overdue')
                                                    Late
                                                @elseif ($order->due_state === 'today')
                                                    Today
                                                @else
                                                    Upcoming
                                                @endif
                                            </span>
                                            {{ optional($order->delivery_date)->format('d M Y') ?? '—' }}
                                        </td>
                                        {{-- <td class="num">Rs {{ number_format($balance, 0) }}</td> --}}
                                        <td><span
                                                class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="report-empty">No pending orders — everything is delivered.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($pendingOrders->hasPages())
                        <div class="cust-pagination" style="margin-top:12px;">
                            @if ($pendingOrders->onFirstPage())
                                <span class="cp-btn cp-disabled">&larr; Prev</span>
                            @else
                                <a href="{{ $pendingOrders->previousPageUrl() }}#worklist" class="cp-btn">&larr; Prev</a>
                            @endif
                            <span class="cp-status">Page {{ $pendingOrders->currentPage() }} of
                                {{ $pendingOrders->lastPage() }} &middot; {{ $pendingOrders->total() }}
                                order{{ $pendingOrders->total() === 1 ? '' : 's' }}</span>
                            @if ($pendingOrders->hasMorePages())
                                <a href="{{ $pendingOrders->nextPageUrl() }}#worklist" class="cp-btn">Next &rarr;</a>
                            @else
                                <span class="cp-btn cp-disabled">Next &rarr;</span>
                            @endif
                        </div>
                    @endif

                    <div style="text-align: center; margin-top: 14px;">
                        <a href="{{ route('report.index') }}" class="btn btn-brass">See All Orders</a>
                    </div>
                </div>

                {{-- RIGHT: Charts stacked vertically --}}
                <div class="dash-grid-charts">
                    <div class="dash-chart-card">
                        <h2 class="dash-subtitle" style="font-size:13px; margin-bottom:10px;">Orders per Day ( Last Month)
                        </h2>
                        <div class="dash-chart-box"><canvas id="ordersChart"></canvas></div>
                    </div>
                    <div class="dash-chart-card">
                        <h2 class="dash-subtitle" style="font-size:13px; margin-bottom:10px;">Amount Received Per Day (Last
                            Month)</h2>
                        <div class="dash-chart-box"><canvas id="collectedChart"></canvas></div>
                    </div>
                </div>

            </div>{{-- /dash-grid --}}

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.querySelectorAll('.dash-chart-box').forEach(function(box) {
                box.innerHTML = '<p style="color:#a63b36;font-size:13px;padding:10px 0;">' +
                    'Chart.js failed to load from the CDN — check your internet connection or ' +
                    'firewall/ad-blocker settings, then refresh this page.</p>';
            });
        } else {

            const chartLabels = @json($chartLabels);

            new Chart(document.getElementById('ordersChart'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Orders booked',
                        data: @json($chartOrders),
                        backgroundColor: 'rgba(176, 134, 58, 0.55)',
                        borderColor: 'rgba(176, 134, 58, 1)',
                        borderWidth: 1,
                        borderRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        }
                    }
                }
            });

            new Chart(document.getElementById('collectedChart'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Amount received',
                        data: @json($chartCollected),
                        fill: true,
                        tension: 0.3,
                        backgroundColor: 'rgba(47, 122, 77, 0.15)',
                        borderColor: 'rgba(47, 122, 77, 1)',
                        pointBackgroundColor: 'rgba(47, 122, 77, 1)',
                        pointRadius: 3,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return 'Rs ' + ctx.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rs ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        }
                    }
                }
            });

        } // end else (Chart.js loaded)
    </script>
@endsection
