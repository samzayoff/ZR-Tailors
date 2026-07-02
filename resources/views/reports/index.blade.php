@extends('layouts.app')

@section('content')
    <main>

        <h1 class="report-title">Report</h1>

        {{-- FILTER BAR --}}
        <form method="GET" action="{{ route('report.index') }}" class="report-filters" id="reportFilters">

            {{-- Calendar range --}}
            <div class="rf-row">
                <div class="cf">
                    <label>From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}">
                </div>
                <div class="cf">
                    <label>To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}">
                </div>
                <div class="cf">
                    <label>Date type</label>
                    <select name="date_field" class="status-select">
                        <option value="booking_date" {{ $dateField === 'booking_date' ? 'selected' : '' }}>Booking date
                        </option>
                        <option value="delivery_date" {{ $dateField === 'delivery_date' ? 'selected' : '' }}>Delivery date
                        </option>
                    </select>
                </div>
            </div>

            {{-- Order status filter --}}
            <div class="rf-row">
                <span class="rf-label">Order status</span>
                <div class="rf-pills">
                    @foreach ($statuses as $s)
                        <label class="rf-pill status-{{ $s }} {{ in_array($s, $selectedStatuses) ? 'on' : '' }}">
                            <input type="checkbox" name="status[]" value="{{ $s }}"
                                {{ in_array($s, $selectedStatuses) ? 'checked' : '' }}>
                            {{ ucfirst($s) }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Payment status filter --}}
            <div class="rf-row">
                <span class="rf-label">Payment status</span>
                <div class="rf-pills">
                    @foreach ($paymentStatuses as $p)
                        <label class="rf-pill pay-{{ $p }} {{ in_array($p, $selectedPayment) ? 'on' : '' }}">
                            <input type="checkbox" name="payment_status[]" value="{{ $p }}"
                                {{ in_array($p, $selectedPayment) ? 'checked' : '' }}>
                            {{ $p === 'partial' ? 'Partial Paid' : ucfirst($p) }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="rf-row rf-actions">
                <button type="submit" class="btn btn-primary">Apply filters</button>
                <a href="{{ route('report.index') }}" class="btn btn-ghost">Reset</a>
            </div>
        </form>

        {{-- =     SUMMARY --}}
        <div class="report-summary">
            <div class="rs-card">
                <span class="rs-k">Orders</span>
                <span class="rs-v num">{{ $summary['count'] }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Total value</span>
                <span class="rs-v num">Rs {{ number_format($summary['total'], 0) }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Collected</span>
                <span class="rs-v num">Rs {{ number_format($summary['collected'], 0) }}</span>
            </div>
            <div class="rs-card">
                <span class="rs-k">Balance due</span>
                <span class="rs-v num">Rs {{ number_format($summary['due'], 0) }}</span>
            </div>
        </div>

        {{-- RESULTS TABLE --}}
        <div class="report-table-wrap">

            <div class="rt-toolbar">

                <div class="searchbar rt-searchbar">
                    <div class="field">
                        <label>Search</label>
                        <input type="text" name="q" form="reportFilters" value="{{ $search }}"
                            placeholder="Suit #, name, or phone…">
                    </div>
                    <button type="submit" form="reportFilters" class="btn btn-primary">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('report.index') }}" class="btn btn-ghost">Clear</a>
                    @endif
                </div>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Suit #</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Booking</th>
                        <th>Delivery</th>
                        <th>Price</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $balance = max(0, $order->price - $order->advance_paid);
                            $payStatus = \App\Http\Controllers\ReportController::paymentStatusFor($order);
                        @endphp
                        <tr>
                            <td class="num">
                                <a
                                    href="{{ route('orders.search', ['q' => $order->order_no]) }}">{{ $order->order_no }}</a>
                            </td>
                            <td>{{ $order->customer->name ?? '—' }}</td>
                            <td class="num">{{ $order->customer->phone ?? '—' }}</td>
                            <td class="num">{{ optional($order->booking_date)->format('d M Y') ?? '—' }}</td>
                            <td class="num">{{ optional($order->delivery_date)->format('d M Y') ?? '—' }}</td>
                            <td class="num">{{ number_format($order->price, 0) }}</td>
                            <td class="num">{{ number_format($order->advance_paid, 0) }}</td>
                            <td class="num">{{ number_format($balance, 0) }}</td>
                            <td><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td><span
                                    class="pay-pill pay-{{ $payStatus }}">{{ $payStatus === 'partial' ? 'Partial Paid' : ucfirst($payStatus) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('orders.search', ['q' => $order->order_no, 'edit' => 1]) }}"
                                    class="row-edit-btn">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="report-empty">No orders match the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </main>

    <script>
        // Auto-submit the filter form whenever a pill or the date-type select changes.
        document.getElementById('reportFilters').addEventListener('change', function(e) {
            if (e.target.matches('input[type="checkbox"], select')) {
                this.submit();
            }
        });
    </script>
@endsection
