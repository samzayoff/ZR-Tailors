@extends('layouts.app')

@section('content')
    <main>
        @php
            // Build a descriptive title based on active filters
            $printParts = [];

            if (!empty($selectedStatuses)) {
                $printParts[] = implode(' / ', array_map('ucfirst', $selectedStatuses)) . ' Orders';
            }
            if (!empty($selectedPayment)) {
                $payLabels = ['paid' => 'Paid', 'unpaid' => 'Unpaid', 'partial' => 'Partial Paid'];
                $printParts[] = implode(' / ', array_map(fn($p) => $payLabels[$p] ?? ucfirst($p), $selectedPayment)) . ' Payment';
            }
            if ($search !== '') {
                $printParts[] = 'Search: "' . $search . '"';
            }

            $printTitle = count($printParts) > 0
                ? implode(' · ', $printParts)
                : 'All Orders';

            $dateLabel = $dateField === 'delivery_date' ? 'Delivery' : 'Booking';
            $printTitle .= ' (' . $dateLabel . ': ' . \Carbon\Carbon::parse($dateFrom)->format('d M Y') . ' – ' . \Carbon\Carbon::parse($dateTo)->format('d M Y') . ')';
        @endphp

        <div class="report-header-wrap" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h1 class="report-title" style="margin: 0;">Report</h1>
            <button type="button" class="btn btn-ghost rt-print-btn" onclick="window.print()">🖨 Print</button>
        </div>

        {{-- PRINT-ONLY dynamic heading (hidden on screen) --}}
        <div class="print-only-heading" style="display:none;">
            <h1 style="font-size: 20pt; margin: 0 0 4px;">ZR Tailors — Report</h1>
            <p style="font-size: 12pt; color: #555; margin: 0;">{{ $printTitle }}</p>
            <hr style="margin: 10px 0; border-color: #ccc;">
        </div>

        {{-- COLLAPSIBLE FILTER BAR --}}
        <div class="rf-collapse-wrap" id="rfCollapseWrap">
            <button type="button" class="rf-toggle-btn" id="rfToggleBtn" onclick="toggleFilters()" aria-expanded="false">
                <span class="rf-toggle-label">Filters &amp; Date Range</span>
                <span class="rf-toggle-arrow" id="rfArrow">▼</span>
            </button>

            <div class="rf-body" id="rfBody" style="display:none;">
                <form method="GET" action="{{ route('report.index') }}" class="report-filters" id="reportFilters">
                    {{-- Preserve sort params across filter submits --}}
                    <input type="hidden" name="sort_by"  value="{{ $sortBy }}">
                    <input type="hidden" name="sort_dir" value="{{ $sortDir }}">

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
                                <option value="booking_date" {{ $dateField === 'booking_date' ? 'selected' : '' }}>Booking date</option>
                                <option value="delivery_date" {{ $dateField === 'delivery_date' ? 'selected' : '' }}>Delivery date</option>
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
            </div>
        </div>

        {{-- SUMMARY --}}
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
        <div class="report-table-wrap" id="report-table">

            <div class="rt-toolbar">

                {{-- ── Sort tab (left side) ────────────────────── --}}
                <div class="rt-sort-tab">
                    <span class="rt-sort-label">Sort by</span>
                    @php
                        $sortFields = [
                            'customer_id' => 'Cust #',
                            'order_no'    => 'Suit #',
                            'balance'     => 'Balance',
                        ];
                    @endphp
                    @foreach ($sortFields as $field => $label)
                        @php
                            $isActive = $sortBy === $field;
                            $nextDir  = ($isActive && $sortDir === 'asc') ? 'desc' : 'asc';
                            $arrow    = $isActive ? ($sortDir === 'asc' ? '↑' : '↓') : '';
                        @endphp
                        <a href="{{ route('report.index', array_merge(request()->except(['sort_by','sort_dir']), ['sort_by' => $field, 'sort_dir' => $nextDir])) }}#report-table"
                           class="rt-sort-btn {{ $isActive ? 'active' : '' }}">
                            {{ $label }}{{ $arrow ? ' '.$arrow : '' }}
                        </a>
                    @endforeach
                </div>

                {{-- ── Search (right side) ─────────────────────── --}}
                <div class="searchbar rt-searchbar">
                    <div class="field">
                        <label>Search</label>
                        <input type="text" name="q" form="reportFilters" value="{{ $search }}"
                            placeholder="Suit #, name, phone, Cust #…">
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
                        <th>Cust #</th>
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
                            $balance   = max(0, $order->price - $order->advance_paid);
                            $payStatus = \App\Http\Controllers\ReportController::paymentStatusFor($order);
                        @endphp
                        <tr>
                            <td class="num">{{ $order->customer?->id ?? '—' }}</td>
                            <td class="num">
                                <a href="{{ route('orders.index', ['q' => $order->order_no]) }}">{{ $order->order_no }}</a>
                            </td>
                            <td>{{ $order->customer?->name ?? '—' }}</td>
                            <td class="num">{{ $order->customer?->phone ?? '—' }}</td>
                            <td class="num">{{ optional($order->booking_date)->format('d M Y') ?? '—' }}</td>
                            <td class="num">{{ optional($order->delivery_date)->format('d M Y') ?? '—' }}</td>
                            <td class="num">{{ number_format($order->price, 0) }}</td>
                            <td class="num">{{ number_format($order->advance_paid, 0) }}</td>
                            <td class="num">{{ number_format($balance, 0) }}</td>
                            <td><span class="status-pill status-{{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                            <td><span class="pay-pill pay-{{ $payStatus }}">{{ $payStatus === 'partial' ? 'Partial Paid' : ucfirst($payStatus) }}</span></td>
                            <td>
                                <a href="{{ route('orders.index', ['q' => $order->order_no]) }}"
                                    class="row-edit-btn">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="report-empty">No orders match the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($orders->hasPages())
            <div class="cust-pagination" style="margin-top: 16px;">
                @if ($orders->onFirstPage())
                    <span class="cp-btn cp-disabled">&larr; Prev</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}#report-table" class="cp-btn">&larr; Prev</a>
                @endif

                <span class="cp-status">
                    Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}
                    &middot; {{ $orders->total() }} order{{ $orders->total() === 1 ? '' : 's' }}
                </span>

                @if ($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}#report-table" class="cp-btn">Next &rarr;</a>
                @else
                    <span class="cp-btn cp-disabled">Next &rarr;</span>
                @endif
            </div>
        @endif

    </main>

    <script>
        // ── Collapsible filter panel ──────────────────────────────────────
        @php
            $hasActiveFilters = !empty($selectedStatuses) || !empty($selectedPayment) || $search !== '';
        @endphp
        var filtersOpenOnLoad = {{ $hasActiveFilters ? 'true' : 'false' }};

        function toggleFilters(forceOpen) {
            var body  = document.getElementById('rfBody');
            var arrow = document.getElementById('rfArrow');
            var btn   = document.getElementById('rfToggleBtn');
            var isOpen = body.style.display !== 'none';

            if (forceOpen === true || (!isOpen && forceOpen !== false)) {
                body.style.display = 'block';
                arrow.textContent  = '▲';
                btn.setAttribute('aria-expanded', 'true');
            } else {
                body.style.display = 'none';
                arrow.textContent  = '▼';
                btn.setAttribute('aria-expanded', 'false');
            }
        }

        // Auto-open filters if active filters are applied (so user sees their settings)
        if (filtersOpenOnLoad) { toggleFilters(true); }

        // Auto-submit the filter form whenever a pill or the date-type select changes.
        document.getElementById('reportFilters').addEventListener('change', function(e) {
            if (e.target.matches('input[type="checkbox"], select')) {
                this.submit();
            }
        });

        // ── Scroll to anchor on page load (keeps user near the table) ────
        (function() {
            if (window.location.hash === '#report-table') {
                var el = document.getElementById('report-table');
                if (el) {
                    // Small delay to let browser finish painting
                    setTimeout(function() {
                        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 80);
                }
            }
        })();
    </script>
@endsection

