@extends('layouts.app')

@section('content')

    {{-- View toggle strip --}}
    <div class="strip">
        <div class="toggle">
            <button id="btnModern" class="on" onclick="setView('modern')">Modern view</button>
            <button id="btnClassic" onclick="setView('classic')">Classic (old system)</button>
        </div>
        <span class="hint">Horizontal worksheet — customer, measurements &amp; design side by side. Stacks on
            mobile.</span>
    </div>

    <main>

        {{-- ============ MODERN VIEW ============ --}}
        <div id="modern">

            {{-- Search bar --}}
            <form method="GET" action="{{ route('orders.search') }}" class="searchbar" id="search">
                <div class="field">
                    <label>Search by suit number or phone</label>
                    <input type="text" name="q" placeholder="6617 / 03100924747" value="{{ $searchQuery ?? '' }}">
                </div>
                <button type="submit" class="btn btn-ghost">Search</button>
                <a href="{{ route('orders.index') }}" class="btn btn-brass">+ New Order</a>
            </form>

            {{-- Validation errors --}}
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

            {{-- ── Order form ── --}}
            @if ($order)
                <form method="POST" action="{{ route('orders.update', $order->id) }}" id="orderForm">
                    @method('PUT')
                @else
                    <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
            @endif
            @csrf

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
                            <label>Customer # <span style="font-weight:400;text-transform:none;color:var(--muted);">(load an
                                    existing customer's details &amp; last measurements)</span></label>
                            <div style="display:flex;gap:8px;">
                                <input class="txt" type="number" min="1" id="customerLookupNo"
                                    placeholder="e.g. 1" style="flex:1;">
                                <button type="button" class="btn btn-ghost" id="customerLookupBtn"
                                    style="flex:0 0 auto;padding:0 14px;">Find</button>
                            </div>
                            <div id="customerLookupResult" class="lookup-status" hidden></div>
                        </div>
                        <div class="cf">
                            <label>Name</label>
                            <input class="txt @error('name') is-invalid @enderror" type="text" name="name"
                                id="customerNameInput" value="{{ old('name', $order?->customer?->name) }}"
                                placeholder="Customer name" required>
                        </div>
                        <div class="cf">
                            <label>S/O · Reference</label>
                            <input class="txt" type="text" name="reference" id="customerReferenceInput"
                                value="{{ old('reference', $order?->customer?->reference) }}" placeholder="—">
                        </div>
                        <div class="cf">
                            <label>Phone</label>
                            <input type="text" name="phone" id="customerPhoneInput"
                                value="{{ old('phone', $order?->customer?->phone) }}" placeholder="03XXXXXXXXX">
                        </div>
                        <div class="cf">
                            <label>Suit / Order No</label>
                            @php $displayOrderNo = $order?->order_no ?? $nextOrderNo; @endphp
                            <input type="hidden" name="order_no" value="{{ $displayOrderNo }}">
                            <input type="text" value="{{ $displayOrderNo }}" readonly
                                style="background:#f5f5f0;cursor:not-allowed;opacity:0.8;">
                        </div>
                        <div class="cf">
                            <label>Booking Date</label>
                            @if ($order)
                                <input class="@error('booking_date') is-invalid @enderror" type="date"
                                    name="booking_date"
                                    value="{{ old('booking_date', $order->booking_date?->format('Y-m-d')) }}"
                                    placeholder="dd/mm/yyyy" required>
                            @else
                                <input type="hidden" name="booking_date"
                                    value="{{ old('booking_date', now()->format('Y-m-d')) }}">
                                <input type="text" value="{{ now()->format('d M Y') }}" readonly
                                    style="background:#f5f5f0;cursor:not-allowed;opacity:0.8;">
                            @endif
                        </div>
                        <div class="cf">
                            <label>Delivery Date</label>
                            <input class="@error('delivery_date') is-invalid @enderror" type="date" name="delivery_date"
                                value="{{ old('delivery_date', $order?->delivery_date?->format('Y-m-d')) }}"
                                placeholder="dd/mm/yyyy" required>
                        </div>
                        <div class="cf">
                            <label>Quantity</label>
                            <input class="@error('quantity') is-invalid @enderror" type="number" name="quantity"
                                min="1" max="99" value="{{ old('quantity', $order?->quantity ?? 1) }}"
                                required>
                        </div>
                        <div class="cf">
                            <label>Price (Rs.)</label>
                            <input class="@error('price') is-invalid @enderror" type="text" name="price"
                                id="priceInput" value="{{ old('price', $order?->price > 0 ? $order->price : '') }}"
                                placeholder="0" oninput="calcRemaining()" required>
                        </div>
                        <div class="cf">
                            <label>Paid (Rs.)</label>
                            <input class="@error('advance_paid') is-invalid @enderror" type="text" name="advance_paid"
                                id="advanceInput" value="{{ old('advance_paid', $order?->advance_paid ?? '0') }}"
                                placeholder="0" oninput="calcRemaining()">
                        </div>

                        {{-- Status dropdown --}}
                        <div class="cf">
                            <label>Status</label>
                            <select name="status" class="status-select" id="statusSelect"
                                onchange="updateStatusBadge(this)">
                                @php
                                    $currentStatus = old('status', $order?->status ?? 'pending');
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
                                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Summary footer --}}
                        <div class="order-summary">
                            <div class="summary-row">
                                <span class="summary-k">Booked</span>
                                <span class="summary-v" id="summBooking">
                                    {{ $order?->booking_date?->format('d M Y') ?? now()->format('d M Y') }}
                                </span>
                            </div>
                            <div class="summary-row">
                                <span class="summary-k">Due</span>
                                <span class="summary-v due-date" id="summDelivery">
                                    {{ $order?->delivery_date?->format('d M Y') ?? '—' }}
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
                        @php $kameezPoints = $garmentTypes->firstWhere('code','kameez')?->measurementPoints ?? collect(); @endphp
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
                        @php $waistcoatPoints = $garmentTypes->firstWhere('code','waistcoat')?->measurementPoints ?? collect(); @endphp
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
                                value="{{ old('colour_note', $order?->colour_note) }}" placeholder="Colour / fabric">
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

                        <textarea name="extra_notes" placeholder="Extra design notes…">{{ old('extra_notes', $order?->extra_notes) }}</textarea>

                        <div class="btn-toggles">
                            @foreach ($designOptions->get('button', collect()) as $opt)
                                @php $isOn = in_array($opt->id, $selectedOptionIds); @endphp
                                <button type="button" class="bt {{ $isOn ? 'on' : '' }}"
                                    data-option-id="{{ $opt->id }}"
                                    onclick="toggleButton(this)">{{ $opt->name_ur }}</button>
                                {{-- Hidden checkbox driven by the toggle button --}}
                                <input type="checkbox" name="design_options[]" value="{{ $opt->id }}"
                                    id="btn_opt_{{ $opt->id }}" {{ $isOn ? 'checked' : '' }} style="display:none">
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>{{-- /sheet --}}

            {{-- Action bar --}}
            <div class="actions">
                @if ($order)
                    <button type="button" class="btn btn-danger mr"
                        onclick="confirmDelete({{ $order->id }})">Delete</button>
                @else
                    <span class="mr"></span>
                @endif

                @if ($order)
                    <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="btn btn-ghost">Print</a>
                @endif

                <a href="{{ route('orders.index') }}" class="btn btn-brass">New Order</a>
                <button type="submit" class="btn btn-primary">Save Order</button>
            </div>

            </form>

            {{-- Delete form (hidden, triggered by JS) --}}
            @if ($order)
                <form id="deleteForm" method="POST" action="{{ route('orders.destroy', $order->id) }}"
                    style="display:none">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

        </div>{{-- /modern --}}


        {{-- ============ CLASSIC VIEW ============ --}}
        <div class="classic" id="classic">

            {{-- Classic search bar --}}
            <form method="GET" action="{{ route('orders.search') }}" class="classic-search" id="classicSearch">
                <input type="text" name="q" placeholder="Search by suit number or phone…"
                    value="{{ $searchQuery ?? '' }}">
                <button type="submit">Search</button>
                <a href="{{ route('orders.index') }}" class="cls-new">+ New Order</a>
            </form>

            {{-- Classic action bar --}}
            @if ($order)
                <div class="classic-actions">
                    <button type="button" class="cls-btn cls-edit" id="clsEditBtn" onclick="classicEdit()">✏
                        Edit</button>
                    <button type="button" class="cls-btn cls-save" id="clsSaveBtn" onclick="classicSave()"
                        style="display:none">💾 Save</button>
                    <button type="button" class="cls-btn cls-cancel" id="clsCancelBtn" onclick="classicCancel()"
                        style="display:none">✕ Cancel</button>
                    @if ($order)
                        <a href="{{ route('orders.print', $order->id) }}" target="_blank" class="cls-btn cls-print">🖨
                            Print</a>
                    @endif
                    <button type="button" class="cls-btn cls-delete" onclick="confirmDelete({{ $order->id }})">🗑
                        Delete</button>
                </div>
            @endif

            {{-- Hidden update form for classic edit --}}
            @if ($order)
                <form id="classicForm" method="POST" action="{{ route('orders.update', $order->id) }}"
                    style="display:none">
                    @csrf
                    @method('PUT')
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
            @endif

            <div class="legacy">
                {{-- Customer column --}}
                <div>
                    {{-- VIEW mode --}}
                    <div class="cls-view">
                        <div class="row"><span class="k">Name</span> <span class="v"
                                id="vw_name">{{ $order?->customer?->name ?? '—' }}</span></div>
                        <div class="row"><span class="k">S/O</span> <span class="v"
                                id="vw_ref">{{ $order?->customer?->reference ?? '—' }}</span></div>
                        <div class="row"><span class="k">Phone</span> <span class="v"
                                id="vw_phone">{{ $order?->customer?->phone ?? '—' }}</span></div>
                        <div class="row"><span class="k">Suit No</span> <span class="v"
                                id="vw_order_no">{{ $order?->order_no ?? $nextOrderNo }}</span></div>
                        <div class="row"><span class="k">Booking</span> <span class="v"
                                id="vw_booking">{{ $order?->booking_date?->format('d/m/Y') ?? '—' }}</span></div>
                        <div class="row"><span class="k">Delivery</span><span class="v"
                                id="vw_delivery">{{ $order?->delivery_date?->format('d/m/Y') ?? '—' }}</span></div>
                        <div class="row"><span class="k">Price</span> <span class="v"
                                id="vw_price">{{ $order?->price > 0 ? 'Rs. ' . number_format($order->price, 0) : '—' }}</span>
                        </div>
                        <div class="row"><span class="k">Paid</span> <span class="v"
                                id="vw_advance">{{ $order?->advance_paid > 0 ? 'Rs. ' . number_format($order->advance_paid, 0) : '—' }}</span>
                        </div>
                        <div class="row"><span class="k">Status</span> <span class="v"
                                id="vw_status">{{ ucfirst($order?->status ?? '—') }}</span></div>
                    </div>

                    {{-- EDIT mode (hidden by default) --}}
                    <div class="cls-edit-fields" style="display:none">
                        <div class="row"><span class="k">Name</span> <input
                                class="cls-input @error('name') is-invalid @enderror" id="ei_name"
                                value="{{ $order?->customer?->name }}"></div>
                        <div class="row"><span class="k">S/O</span> <input class="cls-input" id="ei_ref"
                                value="{{ $order?->customer?->reference }}"></div>
                        <div class="row"><span class="k">Phone</span> <input class="cls-input" id="ei_phone"
                                value="{{ $order?->customer?->phone }}"></div>
                        <div class="row"><span class="k">Suit No</span> <input
                                class="cls-input @error('order_no') is-invalid @enderror" id="ei_order_no"
                                value="{{ $order?->order_no ?? $nextOrderNo }}"></div>
                        <div class="row"><span class="k">Booking</span> <input
                                class="cls-input @error('booking_date') is-invalid @enderror" type="date"
                                id="ei_booking" value="{{ $order?->booking_date?->format('Y-m-d') }}"></div>
                        <div class="row"><span class="k">Delivery</span><input
                                class="cls-input @error('delivery_date') is-invalid @enderror" type="date"
                                id="ei_delivery" value="{{ $order?->delivery_date?->format('Y-m-d') }}"></div>
                        <div class="row"><span class="k">Price</span> <input
                                class="cls-input @error('price') is-invalid @enderror" id="ei_price"
                                value="{{ $order?->price }}"></div>
                        <div class="row"><span class="k">Paid</span> <input
                                class="cls-input @error('advance_paid') is-invalid @enderror" id="ei_advance"
                                value="{{ $order?->advance_paid }}"></div>
                        <div class="row"><span class="k">Status</span>
                            <select class="cls-input cls-select" id="ei_status">
                                @foreach (['pending' => 'Pending', 'stitching' => 'Stitching', 'ready' => 'Ready', 'delivered' => 'Delivered', 'returned' => 'Returned', 'cancelled' => 'Cancelled'] as $v => $l)
                                    <option value="{{ $v }}" {{ $order?->status === $v ? 'selected' : '' }}>
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

    </main>

@endsection

@push('scripts')
    <script>
        // View toggle
        document.getElementById('menuBtn').addEventListener('click', function() {
            document.getElementById('nav').classList.toggle('open');
        });
        document.querySelectorAll('#nav a').forEach(function(a) {
            a.addEventListener('click', function() {
                document.getElementById('nav').classList.remove('open');
            });
        });

        // ── Customer # lookup — loads directly into this order's fields ──
        // Fetches an existing customer and fills their Name / S-O Reference /
        // Phone, plus their last order's measurements straight into the
        // Kameez & Waistcoat measurement inputs on THIS order. Values land in
        // their normal fields (not a separate panel) and stay fully editable
        // afterwards. Price and Paid are never touched — this order's amounts
        // always stay independent of the customer's past orders.
        (function() {
            var input = document.getElementById('customerLookupNo');
            var btn = document.getElementById('customerLookupBtn');
            var status = document.getElementById('customerLookupResult');

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, function(c) {
                    return ({
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#39;'
                    })[c];
                });
            }

            function showStatus(text, isError) {
                status.hidden = false;
                status.textContent = text;
                status.className = 'lookup-status' + (isError ? ' is-error' : ' is-ok');
            }

            function flashField(el) {
                if (!el) return;
                el.classList.remove('just-filled');
                // force reflow so the animation can restart if triggered twice
                void el.offsetWidth;
                el.classList.add('just-filled');
            }

            function fillField(id, value) {
                var el = document.getElementById(id);
                if (!el || value === undefined || value === null) return;
                el.value = value;
                flashField(el);
            }

            function applyCustomer(data) {
                fillField('customerNameInput', data.name || '');
                fillField('customerReferenceInput', data.reference || '');
                fillField('customerPhoneInput', data.phone || '');

                var filledCount = 0;
                (data.garments || []).forEach(function(g) {
                    if (!g.garment_code) return;
                    g.points.forEach(function(p) {
                        if (!p.code) return;
                        var selector = 'input[name="' + g.garment_code + '[' + p.code + ']"]';
                        var el = document.querySelector(selector);
                        if (el) {
                            el.value = p.value;
                            flashField(el);
                            filledCount++;
                        }
                    });
                });

                if (filledCount > 0) {
                    showStatus('Loaded ' + (data.name || 'customer') +
                        ' — name, reference & last measurements applied. You can still edit anything.', false);
                } else {
                    showStatus('Loaded ' + (data.name || 'customer') +
                        ' — no previous measurements on file, so only name/reference were filled.', false);
                }
            }

            function doLookup() {
                var id = (input.value || '').trim();
                if (!id) {
                    status.hidden = true;
                    return;
                }

                showStatus('Looking up customer #' + id + '…', false);

                fetch('/customers/' + encodeURIComponent(id) + '/lookup', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data && data.found === true) {
                            applyCustomer(data);
                        } else {
                            showStatus((data && data.message) || ('No customer found with number #' + id + '.'),
                                true);
                        }
                    })
                    .catch(function() {
                        showStatus('Could not look up that customer. Please try again.', true);
                    });
            }

            btn.addEventListener('click', doLookup);
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doLookup();
                }
            });
        })();

        function setView(v) {
            var m = document.getElementById('modern'),
                c = document.getElementById('classic'),
                bm = document.getElementById('btnModern'),
                bc = document.getElementById('btnClassic');
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

        // Toggle button (ڈبل چاک, دو بٹن, تین بٹن)
        function toggleButton(el) {
            el.classList.toggle('on');
            var id = el.getAttribute('data-option-id');
            var cb = document.getElementById('btn_opt_' + id);
            if (cb) cb.checked = el.classList.contains('on');
        }

        // Delete confirmation
        function confirmDelete(id) {
            if (confirm('Delete this order? This cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }

        // Toast notification
        var t = document.getElementById('toast');

        function toast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            t.classList.add('show');
            clearTimeout(window._tt);
            window._tt = setTimeout(function() {
                t.classList.remove('show');
            }, 1800);
        }

        // Auto-hide flash toast
        var flashToast = document.getElementById('flash-toast');
        if (flashToast) {
            setTimeout(function() {
                flashToast.classList.remove('show');
            }, 2500);
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

        // If we were sent here from the dashboard's "Edit" button
        // (?edit=1), jump straight into edit mode instead of view mode.
        @if ($order)
            if (new URLSearchParams(window.location.search).get('edit') === '1' && document.getElementById('clsEditBtn')) {
                classicEdit();
            }
        @endif

        function classicSave() {
            var f = document.getElementById('classicForm');
            if (!f) return;
            // Copy customer / order fields from edit inputs into hidden form
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
            // Copy measurement inputs
            f.querySelectorAll('input[name^="waistcoat"], input[name^="kameez"]').forEach(function(inp) {
                var code = inp.name.match(/\[([^\]]+)\]/)?.[1];
                var prefix = inp.name.startsWith('waistcoat') ? 'wc' : 'km';
                var src = document.getElementById('ei_' + prefix + '_' + code);
                if (src) inp.value = src.value;
            });
            f.submit();
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

        function updateStatusBadge(sel) {
            var summStatus = document.getElementById('summStatus');
            if (!summStatus) return;
            var val = sel.value;
            var label = statusLabels[val] || val;
            summStatus.innerHTML = '<span class="status-pill status-' + val + '">' + label + '</span>';
        }

        // Wire up date inputs to live summary
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

        // Init on load
        calcRemaining();
    </script>
@endpush
