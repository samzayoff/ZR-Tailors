@extends('layouts.app')

@section('content')

    <main>

        <h1 class="report-title">Customers</h1>

        {{-- SEARCH — by name, phone, or customer --}}
        <form method="GET" action="{{ route('customers.index') }}" class="searchbar">
            <div class="field">
                <label>Search</label>
                <input type="text" name="q" value="{{ $search }}" placeholder="Enter Customer Number">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            @if ($search !== '')
                <a href="{{ route('customers.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </form>

        {{-- CUSTOMER LIST --}}
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Customer #</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Reference</th>
                        <th>Suits</th>
                        <th>Total Value</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        @php
                            $total = (float) ($customer->total_price ?? 0);
                            $paid = (float) ($customer->total_paid ?? 0);
                            $balance = max(0, $total - $paid);
                        @endphp
                        <tr>
                            <td class="num">
                                <a href="{{ route('orders.searchOrder', ['cn' => $customer->id]) }}"
                                    class="cust-no">#{{ $customer->id }}</a>
                            </td>
                            <td>{{ $customer->name }}</td>
                            <td class="num">{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->reference ?? '—' }}</td>
                            <td class="num">{{ $customer->orders_count }}</td>
                            <td class="num">Rs {{ number_format($total, 0) }}</td>
                            <td class="num">Rs {{ number_format($paid, 0) }}</td>
                            <td class="num">Rs {{ number_format($balance, 0) }}</td>
                            <td>
                                <a href="{{ route('orders.searchOrder', ['cn' => $customer->id]) }}"
                                    class="row-edit-btn">View Suits</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="report-empty">
                                @if ($search !== '')
                                    No customers match "{{ $search }}".
                                @else
                                    No customers yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($customers->hasPages())
            <div class="cust-pagination">
                @if ($customers->onFirstPage())
                    <span class="cp-btn cp-disabled">&larr; Prev</span>
                @else
                    <a href="{{ $customers->previousPageUrl() }}" class="cp-btn">&larr; Prev</a>
                @endif

                <span class="cp-status">Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}
                    &middot; {{ $customers->total() }} customer{{ $customers->total() === 1 ? '' : 's' }}</span>

                @if ($customers->hasMorePages())
                    <a href="{{ $customers->nextPageUrl() }}" class="cp-btn">Next &rarr;</a>
                @else
                    <span class="cp-btn cp-disabled">Next &rarr;</span>
                @endif
            </div>
        @endif

    </main>

@endsection
