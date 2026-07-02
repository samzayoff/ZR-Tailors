@extends('layouts.app')

@section('content')

    <div class="strip">

    </div>

    <main>

        {{-- Customer # search bar --}}
        <form method="GET" action="{{ route('orders.searchOrder') }}" class="searchbar" id="customerSearch">
            <div class="field">
                <label>Search by Customer #</label>
                <input type="number" min="1" name="cn" placeholder="Enter Customer Number"
                    value="{{ $cn ?? '' }}" autofocus>
            </div>
            <button type="submit" class="btn btn-ghost">Find Customer</button>
        </form>

        {{-- Validation errors (e.g. after a failed update submit back here) --}}
        @if ($errors->any())
            <style>
                .is-invalid {
                    border: 2px solid #dc3545 !important;
                }
            </style>
            <div class="error-box">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ── Customer summary --}}
        @if (($cn ?? '') !== '')
            @if ($customerSummary && $customerSummary['found'])
                @php
                    $cs = $customerSummary;
                    $c = $cs['customer'];

                    $words = explode(' ', trim($c->name));
                    $initials = '';

                    foreach ($words as $word) {
                        if ($word != '') {
                            $initials .= strtoupper(substr($word, 0, 1));

                            if (strlen($initials) == 2) {
                                break;
                            }
                        }
                    }
                @endphp

                <div class="customer-profile-card">
                    <div class="cp-avatar">{{ $initials ?: '—' }}</div>
                    <div class="cp-info">
                        <div class="cp-name-row">
                            <span class="cp-name">{{ $c->name }}</span>
                            <span class="cp-badge">Customer #{{ $c->id }}</span>
                        </div>
                        @if ($c->phone || $c->reference)
                            <div class="cp-sub">
                                @if ($c->phone)
                                    <span class="num">{{ $c->phone }}</span>
                                @endif
                                @if ($c->phone && $c->reference)
                                    <span class="dot">·</span>
                                @endif
                                @if ($c->reference)
                                    <span>{{ $c->reference }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="report-summary">
                    <div class="rs-card">
                        <span class="rs-k">Orders</span>
                        <span class="rs-v num">{{ $cs['orders_count'] }}</span>
                    </div>
                    <div class="rs-card">
                        <span class="rs-k">Total value</span>
                        <span class="rs-v num">Rs {{ number_format($cs['total_price'], 0) }}</span>
                    </div>
                    <div class="rs-card">
                        <span class="rs-k">Collected</span>
                        <span class="rs-v num">Rs {{ number_format($cs['total_paid'], 0) }}</span>
                    </div>
                    <div class="rs-card">
                        <span class="rs-k">Balance due</span>
                        <span class="rs-v num">Rs {{ number_format($cs['total_remaining'], 0) }}</span>
                    </div>
                </div>

                <div class="report-table-wrap" style="margin-bottom:20px;">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Suit #</th>
                                <th>Booking</th>
                                <th>Delivery</th>
                                <th>Price</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cs['orders'] as $o)
                                @php $balance = max(0, $o->price - $o->advance_paid); @endphp
                                <tr>
                                    <td class="num">{{ $o->order_no }}</td>
                                    <td class="num">{{ optional($o->booking_date)->format('d M Y') ?? '—' }}</td>
                                    <td class="num">{{ optional($o->delivery_date)->format('d M Y') ?? '—' }}</td>
                                    <td class="num">{{ number_format($o->price, 0) }}</td>
                                    <td class="num">{{ number_format($o->advance_paid, 0) }}</td>
                                    <td class="num">{{ number_format($balance, 0) }}</td>
                                    <td><span
                                            class="status-pill status-{{ $o->status }}">{{ ucfirst($o->status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('orders.searchOrder', ['q' => $o->order_no]) }}"
                                            class="row-edit-btn">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="report-empty">This customer has no orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="customer-lookup-panel" style="margin-top:4px;margin-bottom:16px;">
                    <div class="clp-empty">No customer found with number #{{ $customerSummary['cn'] ?? $cn }}.</div>
                </div>
            @endif
        @endif

        @if (!$order)
            @if (($cn ?? '') === '')
                {{-- No order loaded: nothing searched yet --}}
                <div class="customer-lookup-panel" style="margin-top:4px;">
                    <div class="clp-empty">Enter a customer number above to see their orders, then hit Edit on the
                        one you want to update.</div>
                </div>
            @endif
        @else
            {{-- ── Order form (UPDATE ONLY — never creates a new order) ── --}}
            <form method="POST" action="{{ route('orders.update', $order->id) }}" id="orderForm">
                @method('PUT')
                @csrf
                <input type="hidden" name="return_to" value="lookup">

                <div class="sheet" id="c-order">

                    {{-- COL 1 — Customer --}}
                    <div class="col">
                        <div class="col-head">
                            <svg class="ci">
                                <use href="#i-pen" />
                            </svg>
                            <span class="ce">Customer</span>
                        </div>
                        <div class="col-body">
                            <div class="cf">
                                <label>Name</label>
                                <input class="txt @error('name') is-invalid @enderror" type="text" name="name"
                                    value="{{ old('name', $order->customer?->name) }}" placeholder="Customer name"
                                    required>
                            </div>
                            <div class="cf">
                                <label>S/O · Reference</label>
                                <input class="txt" type="text" name="reference"
                                    value="{{ old('reference', $order->customer?->reference) }}" placeholder="—">
                            </div>
                            <div class="cf">
                                <label>Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $order->customer?->phone) }}"
                                    placeholder="03XXXXXXXXX">
                            </div>
                            <div class="cf">
                                <label>Suit / Order No</label>
                                <input type="hidden" name="order_no" value="{{ $order->order_no }}">
                                <input type="text" value="{{ $order->order_no }}" readonly
                                    style="background:#f5f5f0;cursor:not-allowed;opacity:0.8;">
                            </div>
                            <div class="cf">
                                <label>Booking Date</label>
                                <input class="@error('booking_date') is-invalid @enderror" type="date"
                                    name="booking_date"
                                    value="{{ old('booking_date', $order->booking_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="cf">
                                <label>Delivery Date</label>
                                <input class="@error('delivery_date') is-invalid @enderror" type="date"
                                    name="delivery_date"
                                    value="{{ old('delivery_date', $order->delivery_date?->format('Y-m-d')) }}" required>
                            </div>
                            <div class="cf">
                                <label>Quantity</label>
                                <input class="@error('quantity') is-invalid @enderror" type="number" name="quantity"
                                    min="1" max="99" value="{{ old('quantity', $order->quantity ?? 1) }}"
                                    required>
                            </div>
                            <div class="cf">
                                <label>Price (Rs.)</label>
                                <input class="@error('price') is-invalid @enderror" type="text" name="price"
                                    id="priceInput" value="{{ old('price', $order->price > 0 ? $order->price : '') }}"
                                    placeholder="0" oninput="calcRemaining()" required>
                            </div>
                            <div class="cf">
                                <label>Paid (Rs.)</label>
                                <input class="@error('advance_paid') is-invalid @enderror" type="text"
                                    name="advance_paid" id="advanceInput"
                                    value="{{ old('advance_paid', $order->advance_paid ?? '0') }}" placeholder="0"
                                    oninput="calcRemaining()">
                            </div>

                            <div class="cf">
                                <label>Status</label>
                                <select name="status" class="status-select" id="statusSelect"
                                    onchange="updateStatusBadge(this)">
                                    @php
                                        $currentStatus = old('status', $order->status ?? 'pending');
                                        $statuses = [
                                            'pending' => 'Pending',
                                            'stitching' => 'Stitching',
                                            'ready' => 'Ready',
                                            'delivered' => 'Delivered',
                                            'returned' => 'Returned',
                                            'cancelled' => 'Cancelled',
                                        ];
                                    @endphp
                                    @foreach ($statuses as $val => $label)
                                        <option value="{{ $val }}"
                                            {{ $currentStatus === $val ? 'selected' : '' }}>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span class="summary-k">Booked</span>
                                    <span class="summary-v" id="summBooking">
                                        {{ $order->booking_date?->format('d M Y') ?? '—' }}
                                    </span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-k">Due</span>
                                    <span class="summary-v due-date" id="summDelivery">
                                        {{ $order->delivery_date?->format('d M Y') ?? '—' }}
                                    </span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-k">Status</span>
                                    <span class="summary-v" id="summStatus">
                                        <span
                                            class="status-pill status-{{ $currentStatus }}">{{ $statuses[$currentStatus] }}</span>
                                    </span>
                                </div>
                                <div class="summary-row remaining-row">
                                    <span class="summary-k">Remaining</span>
                                    <span class="summary-v remaining-amt" id="summRemaining">—</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- COL 2 — Kameez --}}
                    <div class="col" id="c-suit">
                        <div class="col-head">
                            <svg class="ci">
                                <use href="#i-kameez" />
                            </svg>
                            <span class="ce">Kameez</span>
                        </div>
                        <div class="ruler"></div>
                        <div class="col-body">
                            @php $kameezPoints = $garmentTypes->firstWhere('code', 'kameez')?->measurementPoints ?? collect(); @endphp
                            @foreach ($kameezPoints as $point)
                                <div class="mrow">
                                    <input class="num" type="text" name="kameez[{{ $point->code }}]"
                                        value="{{ old("kameez.{$point->code}", $measurements['kameez'][$point->code] ?? '') }}"
                                        placeholder="—">
                                    <div class="ml">
                                        <span class="u ur">{{ $point->name_ur }}</span>
                                        @if ($point->icon)
                                            <svg class="ico">
                                                <use href="#{{ $point->icon }}" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- COL 3 — Waistcoat --}}
                    <div class="col" id="c-wsk">
                        <div class="col-head">
                            <svg class="ci">
                                <use href="#i-vest" />
                            </svg>
                            <span class="ce">Waistcoat</span>
                        </div>
                        <div class="ruler"></div>
                        <div class="col-body">
                            @php $waistcoatPoints = $garmentTypes->firstWhere('code', 'waistcoat')?->measurementPoints ?? collect(); @endphp
                            @foreach ($waistcoatPoints as $point)
                                <div class="mrow">
                                    <input class="num" type="text" name="waistcoat[{{ $point->code }}]"
                                        value="{{ old("waistcoat.{$point->code}", $measurements['waistcoat'][$point->code] ?? '') }}"
                                        placeholder="—">
                                    <div class="ml">
                                        <span class="u ur">{{ $point->name_ur }}</span>
                                        @if ($point->icon)
                                            <svg class="ico">
                                                <use href="#{{ $point->icon }}" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- COL 4 — Stitching --}}
                    <div class="col">
                        <div class="col-head">
                            <svg class="ci">
                                <use href="#i-needle" />
                            </svg>
                            <span class="ce">Stitching</span>
                        </div>
                        <div class="col-body">

                            <div class="cgrp">Stitch type</div>
                            @foreach ($designOptions->get('stitch', collect()) as $opt)
                                <label class="copt">
                                    <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                        {{ in_array($opt->id, $selectedOptionIds) ? 'checked' : '' }}>
                                    <span class="u ur">{{ $opt->name_ur }}</span>
                                </label>
                            @endforeach

                            <div class="cgrp">Cuff &amp; Kaaj</div>
                            @foreach ($designOptions->get('cuff_kaaj', collect()) as $opt)
                                <label class="copt">
                                    <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                        {{ in_array($opt->id, $selectedOptionIds) ? 'checked' : '' }}>
                                    <span class="u ur">{{ $opt->name_ur }}</span>
                                </label>
                            @endforeach

                            <div class="cgrp">Extras</div>
                            @foreach ($designOptions->get('extra', collect()) as $opt)
                                <label class="copt">
                                    <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                        {{ in_array($opt->id, $selectedOptionIds) ? 'checked' : '' }}>
                                    <span class="u ur">{{ $opt->name_ur }}</span>
                                </label>
                            @endforeach

                        </div>
                    </div>

                    {{-- COL 5 — Design --}}
                    <div class="col">
                        <div class="col-head">
                            <svg class="ci">
                                <use href="#i-collar" />
                            </svg>
                            <span class="ce">Design</span>
                        </div>
                        <div class="col-body">

                            <div class="scolor">
                                <label>Write for colour</label>
                                <input type="text" name="colour_note"
                                    value="{{ old('colour_note', $order->colour_note) }}" placeholder="Colour / fabric">
                            </div>

                            <div class="cgrp">Collar &amp; Cuff style</div>
                            <div class="style-grid">
                                @foreach ($designOptions->get('style', collect()) as $opt)
                                    <label class="sopt">
                                        <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                            {{ in_array($opt->id, $selectedOptionIds) ? 'checked' : '' }}>
                                        <span class="u ur">{{ $opt->name_ur }}</span>
                                        @if (!empty($opt->icon))
                                            <svg class="ico">
                                                <use href="#{{ $opt->icon }}" />
                                            </svg>
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            <div class="sketch">
                                <svg class="ico">
                                    <use href="#i-pen" />
                                </svg>
                                <span>Design sketch area</span>
                            </div>

                            <textarea name="extra_notes" placeholder="Extra design notes…">{{ old('extra_notes', $order->extra_notes) }}</textarea>

                            <div class="btn-toggles">
                                @foreach ($designOptions->get('button', collect()) as $opt)
                                    @php $isOn = in_array($opt->id, $selectedOptionIds); @endphp
                                    <button type="button" class="bt {{ $isOn ? 'on' : '' }}"
                                        data-option-id="{{ $opt->id }}"
                                        onclick="toggleButton(this)">{{ $opt->name_ur }}</button>
                                    <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                        id="btn_opt_{{ $opt->id }}" {{ $isOn ? 'checked' : '' }}
                                        style="display:none">
                                @endforeach
                            </div>

                        </div>
                    </div>

                </div>{{-- /sheet --}}

                {{-- Action bar — view & update only, no "save as new" path exists here --}}
                <div class="actions">
                    <button type="button" class="btn btn-danger mr" onclick="confirmDelete({{ $order->id }})">
                        Delete</button>
                    <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="btn btn-ghost">Print</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>

            </form>

            {{-- Delete form (hidden, triggered by JS) --}}
            <form id="deleteForm" method="POST" action="{{ route('orders.destroy', $order->id) }}"
                style="display:none">
                @csrf
                @method('DELETE')
            </form>
        @endif

    </main>

@endsection

@push('scripts')
    <script>
        function toggleButton(el) {
            el.classList.toggle('on');
            var id = el.getAttribute('data-option-id');
            var cb = document.getElementById('btn_opt_' + id);
            if (cb) cb.checked = el.classList.contains('on');
        }

        function confirmDelete(id) {
            if (confirm('Delete this order? This cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        function calcRemaining() {
            var price = parseFloat(document.getElementById('priceInput')?.value) || 0;
            var advance = parseFloat(document.getElementById('advanceInput')?.value) || 0;
            var rem = document.getElementById('summRemaining');
            if (!rem) return;
            if (price === 0) {
                rem.textContent = '—';
                rem.className = 'summary-v remaining-amt';
                return;
            }
            var remaining = price - advance;
            rem.textContent = 'Rs. ' + remaining.toLocaleString();
            rem.className = 'summary-v remaining-amt' + (remaining <= 0 ? ' paid' : '');
        }

        var statusLabels = {
            pending: 'Pending',
            stitching: 'Stitching',
            ready: 'Ready',
            delivered: 'Delivered',
            returned: 'Returned',
            cancelled: 'Cancelled'
        };

        function formatDate(val) {
            if (!val) return '—';
            var d = new Date(val);
            if (isNaN(d)) return '—';
            return d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function updateStatusBadge(sel) {
            var summStatus = document.getElementById('summStatus');
            if (!summStatus) return;
            var val = sel.value;
            var label = statusLabels[val] || val;
            summStatus.innerHTML = '<span class="status-pill status-' + val + '">' + label + '</span>';
        }

        var bookingInput = document.querySelector('input[name="booking_date"]');
        var deliveryInput = document.querySelector('input[name="delivery_date"]');
        if (bookingInput) bookingInput.addEventListener('change', function() {
            var el = document.getElementById('summBooking');
            if (el) el.textContent = formatDate(this.value) || '—';
        });
        if (deliveryInput) deliveryInput.addEventListener('change', function() {
            var el = document.getElementById('summDelivery');
            if (el) el.textContent = formatDate(this.value) || '—';
        });

        // Auto-hide flash toast
        var flashToast = document.getElementById('flash-toast');
        if (flashToast) {
            setTimeout(function() {
                flashToast.classList.remove('show');
            }, 2500);
        }

        calcRemaining();
    </script>
@endpush
