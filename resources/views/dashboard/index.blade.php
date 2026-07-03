@extends('layouts.app')

@section('content')
    <main>

        <h1 class="report-title">Dashboard — {{ $today->format('d M Y') }}</h1>

        {{-- TODAY'S STAT CARDS --}}
        <div class="report-summary dash-summary dash-summary-6">

            <div class="rs-card">
                <span class="rs-k">Today's Sales</span>
                <span class="rs-v num">Rs {{ number_format($todaySales, 0) }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Cash Collected Today</span>
                <span class="rs-v num">Rs {{ number_format($todayCollected, 0) }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Balance Due (All Customers)</span>
                <span class="rs-v num">Rs {{ number_format($dueBalance, 0) }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Total Customers</span>
                <span class="rs-v num">{{ $totalCustomers }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Orders Booked Today</span>
                <span class="rs-v num">{{ $ordersToday }}</span>
            </div>

            <div class="rs-card">
                <span class="rs-k">Due For Delivery Today</span>
                <span class="rs-v num">{{ $dueTodayCount }}</span>
            </div>



        </div>

        {{-- CHARTS — this mont --}}
        <div class="dash-charts">
            <div class="dash-chart-card">
                <h2 class="dash-subtitle">Orders Per Day — Last 30 Days</h2>
                <div class="dash-chart-box"><canvas id="ordersChart"></canvas></div>
            </div>
            <div class="dash-chart-card">
                <h2 class="dash-subtitle">Amount Received Per Day — Last 30 Days</h2>
                <div class="dash-chart-box"><canvas id="collectedChart"></canvas></div>
            </div>
        </div>

        {{-- TODAY'S DUE ORDERS TABLE
        --}}
        <h2 class="dash-subtitle">Orders Due Today</h2>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Suit #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Booking</th>
                        <th>Price</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dueOrders as $order)
                        @php $balance = max(0, $order->price - $order->advance_paid); @endphp
                        <tr>
                            <td class="num">
                                <a
                                    href="{{ route('orders.updateOrder', ['q' => $order->order_no]) }}">{{ $order->order_no }}</a>
                            </td>
                            <td>{{ $order->customer->name ?? '—' }}</td>
                            <td class="num">{{ $order->customer->phone ?? '—' }}</td>
                            <td class="num">{{ optional($order->booking_date)->format('d M Y') ?? '—' }}</td>
                            <td class="num">{{ number_format($order->price, 0) }}</td>
                            <td class="num">{{ number_format($order->advance_paid, 0) }}</td>
                            <td class="num">{{ number_format($balance, 0) }}</td>
                            <td><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('orders.updateOrder', ['q' => $order->order_no]) }}"
                                    class="row-edit-btn">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="report-empty">No orders are due for delivery today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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
