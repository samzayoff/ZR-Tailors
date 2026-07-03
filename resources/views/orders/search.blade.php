@extends('layouts.app')

@section('content')

    <div class="strip">
        <div class="toggle">
            <button id="btnModern" class="on" onclick="setView('modern')">Modern view</button>
            <button id="btnClassic" onclick="setView('classic')">Classic (old system)</button>
        </div>
    </div>

    <main>

        {{-- Customer # search bar --}}
        <form method="GET" action="{{ route('orders.searchOrder') }}" class="searchbar" id="customerSearch">
            <div class="field">
                <label>Search by Customer Number</label>
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

        {{-- ── Customer summary: name, order count, amounts --}}
        @if (($cn ?? '') !== '')
            @if ($customerSummary && $customerSummary['found'])
                @php
                    $cs = $customerSummary;
                    $c = $cs['customer'];
                    $initials = collect(preg_split('/\s+/', trim($c->name)))
                        ->filter()
                        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->take(2)
                        ->implode('');
                @endphp

                <div class="customer-profile-card">
                    <div class="cp-avatar">{{ $initials ?: '—' }}</div>
                    <div class="cp-info">
                        <div class="cp-name-row">
                            <span class="cp-name">{{ $c->name }}</span>
                        </div>
                        @if ($c->phone || $c->reference)
                            <div class="cp-sub">
                                @if ($c->phone)
                                    <span class="num">{{ $c->phone }}</span>
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

                </div>
            @endif
        @else
            {{-- MODERN VIEW --}}
            <div id="modern">

                {{-- Order form --}}
                <form method="POST" action="{{ route('orders.update', $order->id) }}" id="orderForm">
                    @method('PUT')
                    @csrf
                    <input type="hidden" name="return_to" value="lookup">

                    <div class="sheet" id="c-order">

                        {{-- COL 1 Customer --}}
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
                                    <input type="text" name="phone"
                                        value="{{ old('phone', $order->customer?->phone) }}" placeholder="03XXXXXXXXX">
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
                                        value="{{ old('booking_date', $order->booking_date?->format('Y-m-d')) }}"
                                        required>
                                </div>
                                <div class="cf">
                                    <label>Delivery Date</label>
                                    <input class="@error('delivery_date') is-invalid @enderror" type="date"
                                        name="delivery_date"
                                        value="{{ old('delivery_date', $order->delivery_date?->format('Y-m-d')) }}"
                                        required>
                                </div>
                                <div class="cf">
                                    <label>Quantity</label>
                                    <input class="@error('quantity') is-invalid @enderror" type="number" name="quantity"
                                        min="1" max="99"
                                        value="{{ old('quantity', $order->quantity ?? 1) }}" required>
                                </div>
                                <div class="cf">
                                    <label>Price (Rs.)</label>
                                    <input class="@error('price') is-invalid @enderror" type="text" name="price"
                                        id="priceInput"
                                        value="{{ old('price', $order->price > 0 ? $order->price : '') }}"
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
                                        value="{{ old('colour_note', $order->colour_note) }}"
                                        placeholder="Colour / fabric">
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
                        <a href="{{ route('orders.print', $order->id) }}" target="_blank"
                            class="btn btn-ghost">Print</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>

                </form>

            </div>{{-- /modern --}}

            {{-- ============ CLASSIC VIEW ============ --}}
            <div class="classic" id="classic">

                <div class="classic-actions">
                    <button type="button" class="cls-btn cls-edit" id="clsEditBtn" onclick="classicEdit()">✏
                        Edit</button>
                    <button type="button" class="cls-btn cls-save" id="clsSaveBtn" onclick="classicSave()"
                        style="display:none">💾 Save</button>
                    <button type="button" class="cls-btn cls-cancel" id="clsCancelBtn" onclick="classicCancel()"
                        style="display:none">✕ Cancel</button>
                    <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="cls-btn cls-print">🖨
                        Print</a>
                    <button type="button" class="cls-btn cls-delete" onclick="confirmDelete({{ $order->id }})">🗑
                        Delete</button>
                </div>

                {{-- Hidden update form for classic edit --}}
                <form id="classicForm" method="POST" action="{{ route('orders.update', $order->id) }}"
                    style="display:none">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="return_to" value="lookup">
                    <input type="hidden" name="name" id="cf_name" value="{{ $order->customer->name }}">
                    <input type="hidden" name="reference" id="cf_reference" value="{{ $order->customer->reference }}">
                    <input type="hidden" name="phone" id="cf_phone" value="{{ $order->customer->phone }}">
                    <input type="hidden" name="order_no" id="cf_order_no" value="{{ $order->order_no }}">
                    <input type="hidden" name="booking_date" id="cf_booking_date"
                        value="{{ $order->booking_date?->format('Y-m-d') }}">
                    <input type="hidden" name="delivery_date" id="cf_delivery_date"
                        value="{{ $order->delivery_date?->format('Y-m-d') }}">
                    <input type="hidden" name="quantity" id="cf_quantity" value="{{ $order->quantity }}">
                    <input type="hidden" name="price" id="cf_price" value="{{ $order->price }}">
                    <input type="hidden" name="advance_paid" id="cf_advance" value="{{ $order->advance_paid }}">
                    <input type="hidden" name="status" id="cf_status" value="{{ $order->status }}">
                    <input type="hidden" name="colour_note" id="cf_colour" value="{{ $order->colour_note }}">
                    <input type="hidden" name="extra_notes" id="cf_notes" value="{{ $order->extra_notes }}">
                    @foreach ($selectedOptionIds as $oid)
                        <input type="hidden" name="design_options[]" value="{{ $oid }}">
                    @endforeach
                    @foreach (['kameez', 'waistcoat'] as $gc)
                        @foreach ($measurements[$gc] ?? [] as $code => $val)
                            <input type="hidden" name="{{ $gc }}[{{ $code }}]"
                                value="{{ $val }}">
                        @endforeach
                    @endforeach
                </form>

                <div class="legacy">
                    {{-- Customer column --}}
                    <div>
                        {{-- VIEW mode --}}
                        <div class="cls-view">
                            <div class="row"><span class="k">Name</span> <span class="v"
                                    id="vw_name">{{ $order->customer?->name ?? '—' }}</span></div>
                            <div class="row"><span class="k">S/O</span> <span class="v"
                                    id="vw_ref">{{ $order->customer?->reference ?? '—' }}</span></div>
                            <div class="row"><span class="k">Phone</span> <span class="v"
                                    id="vw_phone">{{ $order->customer?->phone ?? '—' }}</span></div>
                            <div class="row"><span class="k">Suit No</span> <span class="v"
                                    id="vw_order_no">{{ $order->order_no }}</span></div>
                            <div class="row"><span class="k">Booking</span> <span class="v"
                                    id="vw_booking">{{ $order->booking_date?->format('d/m/Y') ?? '—' }}</span></div>
                            <div class="row"><span class="k">Delivery</span><span class="v"
                                    id="vw_delivery">{{ $order->delivery_date?->format('d/m/Y') ?? '—' }}</span></div>
                            <div class="row"><span class="k">Price</span> <span class="v"
                                    id="vw_price">{{ $order->price > 0 ? 'Rs. ' . number_format($order->price, 0) : '—' }}</span>
                            </div>
                            <div class="row"><span class="k">Paid</span> <span class="v"
                                    id="vw_advance">{{ $order->advance_paid > 0 ? 'Rs. ' . number_format($order->advance_paid, 0) : '—' }}</span>
                            </div>
                            <div class="row"><span class="k">Status</span> <span class="v"
                                    id="vw_status">{{ ucfirst($order->status ?? '—') }}</span></div>
                        </div>

                        {{-- EDIT mode (hidden by default) --}}
                        <div class="cls-edit-fields" style="display:none">
                            <div class="row"><span class="k">Name</span> <input
                                    class="cls-input @error('name') is-invalid @enderror" id="ei_name"
                                    value="{{ $order->customer?->name }}"></div>
                            <div class="row"><span class="k">S/O</span> <input class="cls-input" id="ei_ref"
                                    value="{{ $order->customer?->reference }}"></div>
                            <div class="row"><span class="k">Phone</span> <input class="cls-input"
                                    id="ei_phone" value="{{ $order->customer?->phone }}"></div>
                            <div class="row"><span class="k">Suit No</span> <input
                                    class="cls-input @error('order_no') is-invalid @enderror" id="ei_order_no"
                                    value="{{ $order->order_no }}"></div>
                            <div class="row"><span class="k">Booking</span> <input
                                    class="cls-input @error('booking_date') is-invalid @enderror" type="date"
                                    id="ei_booking" value="{{ $order->booking_date?->format('Y-m-d') }}"></div>
                            <div class="row"><span class="k">Delivery</span><input
                                    class="cls-input @error('delivery_date') is-invalid @enderror" type="date"
                                    id="ei_delivery" value="{{ $order->delivery_date?->format('Y-m-d') }}"></div>
                            <div class="row"><span class="k">Price</span> <input
                                    class="cls-input @error('price') is-invalid @enderror" id="ei_price"
                                    value="{{ $order->price }}"></div>
                            <div class="row"><span class="k">Paid</span> <input
                                    class="cls-input @error('advance_paid') is-invalid @enderror" id="ei_advance"
                                    value="{{ $order->advance_paid }}"></div>
                            <div class="row"><span class="k">Status</span>
                                <select class="cls-input cls-select" id="ei_status">
                                    @foreach (['pending' => 'Pending', 'stitching' => 'Stitching', 'ready' => 'Ready', 'delivered' => 'Delivered', 'returned' => 'Returned', 'cancelled' => 'Cancelled'] as $v => $l)
                                        <option value="{{ $v }}"
                                            {{ $order->status === $v ? 'selected' : '' }}>
                                            {{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Waistcoat measurements --}}
                    <div>
                        <div class="col-h head-cell" style="margin:-8px -10px 8px;">Waistcoat Size</div>
                        @foreach ($garmentTypes->firstWhere('code', 'waistcoat')?->measurementPoints ?? [] as $point)
                            <div class="row">
                                <span class="k ur">{{ $point->name_ur }}</span>
                                <span class="cls-view v">{{ $measurements['waistcoat'][$point->code] ?? '—' }}</span>
                                <input class="cls-edit-fields cls-input num" style="display:none;width:60px"
                                    id="ei_wc_{{ $point->code }}"
                                    value="{{ $measurements['waistcoat'][$point->code] ?? '' }}" placeholder="—">
                            </div>
                        @endforeach
                    </div>

                    {{-- Kameez measurements --}}
                    <div>
                        <div class="col-h head-cell" style="margin:-8px -10px 8px;">Kameez Size</div>
                        @foreach ($garmentTypes->firstWhere('code', 'kameez')?->measurementPoints ?? [] as $point)
                            <div class="row">
                                <span class="k ur">{{ $point->name_ur }}</span>
                                <span class="cls-view v">{{ $measurements['kameez'][$point->code] ?? '—' }}</span>
                                <input class="cls-edit-fields cls-input num" style="display:none;width:60px"
                                    id="ei_km_{{ $point->code }}"
                                    value="{{ $measurements['kameez'][$point->code] ?? '' }}" placeholder="—">
                            </div>
                        @endforeach
                    </div>

                    {{-- Design options --}}
                    <div>
                        <div class="col-h head-cell" style="margin:-8px -10px 8px;">Design</div>

                        @foreach ($designOptions->get('stitch', collect()) as $opt)
                            @if ($opt->code === 'zanjeeri')
                                <div class="chk">
                                    <i
                                        class="{{ in_array($opt->id, $selectedOptionIds) ? 'on' : '' }}">{{ in_array($opt->id, $selectedOptionIds) ? '✓' : '' }}</i>
                                    <span class="ur">{{ $opt->name_ur }}</span>
                                </div>
                            @endif
                        @endforeach

                        @foreach ($designOptions->get('cuff_kaaj', collect()) as $opt)
                            <div class="chk">
                                <i
                                    class="{{ in_array($opt->id, $selectedOptionIds) ? 'on' : '' }}">{{ in_array($opt->id, $selectedOptionIds) ? '✓' : '' }}</i>
                                <span class="ur">{{ $opt->name_ur }}</span>
                            </div>
                        @endforeach

                        @foreach ($designOptions->get('extra', collect()) as $opt)
                            @if (in_array($opt->code, ['shalwar_pocket', 'no_name', 'make_drawing']))
                                <div class="chk">
                                    <i
                                        class="{{ in_array($opt->id, $selectedOptionIds) ? 'on' : '' }}">{{ in_array($opt->id, $selectedOptionIds) ? '✓' : '' }}</i>
                                    <span class="ur">{{ $opt->name_ur }}</span>
                                </div>
                            @endif
                        @endforeach

                        @foreach ($designOptions->get('button', collect()) as $opt)
                            <div class="chk">
                                <i
                                    class="{{ in_array($opt->id, $selectedOptionIds) ? 'on' : '' }}">{{ in_array($opt->id, $selectedOptionIds) ? '✓' : '' }}</i>
                                <span class="ur">{{ $opt->name_ur }}</span>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>{{-- /classic --}}

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
        function setView(v) {
            var m = document.getElementById('modern'),
                c = document.getElementById('classic'),
                bm = document.getElementById('btnModern'),
                bc = document.getElementById('btnClassic');
            if (!m || !c || !bm || !bc) return;
            if (v === 'classic') {
                m.style.display = 'none';
                c.style.display = 'block';
                bc.classList.add('on');
                bm.classList.remove('on');
            } else {
                m.style.display = 'block';
                c.style.display = 'none';
                bm.classList.add('on');
                bc.classList.remove('on');
            }
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // ── Classic view edit/save/cancel ───────────────────────────
        function classicEdit() {
            document.querySelectorAll('.cls-view').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.cls-edit-fields').forEach(el => el.style.display = '');
            document.getElementById('clsEditBtn').style.display = 'none';
            document.getElementById('clsSaveBtn').style.display = '';
            document.getElementById('clsCancelBtn').style.display = '';
        }

        function classicCancel() {
            document.querySelectorAll('.cls-view').forEach(el => el.style.display = '');
            document.querySelectorAll('.cls-edit-fields').forEach(el => el.style.display = 'none');
            document.getElementById('clsEditBtn').style.display = '';
            document.getElementById('clsSaveBtn').style.display = 'none';
            document.getElementById('clsCancelBtn').style.display = 'none';
        }

        function classicSave() {
            var f = document.getElementById('classicForm');
            if (!f) return;
            var map = {
                'cf_name': 'ei_name',
                'cf_reference': 'ei_ref',
                'cf_phone': 'ei_phone',
                'cf_order_no': 'ei_order_no',
                'cf_booking_date': 'ei_booking',
                'cf_delivery_date': 'ei_delivery',
                'cf_price': 'ei_price',
                'cf_advance': 'ei_advance',
                'cf_status': 'ei_status',
            };
            for (var hid in map) {
                var src = document.getElementById(map[hid]);
                var dst = document.getElementById(hid);
                if (src && dst) dst.value = src.value;
            }
            f.querySelectorAll('input[name^="waistcoat"], input[name^="kameez"]').forEach(function(inp) {
                var code = inp.name.match(/\[([^\]]+)\]/)?.[1];
                var prefix = inp.name.startsWith('waistcoat') ? 'wc' : 'km';
                var src = document.getElementById('ei_' + prefix + '_' + code);
                if (src) inp.value = src.value;
            });
            f.submit();
        }

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
