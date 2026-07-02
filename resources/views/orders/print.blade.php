<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<title>Order #{{ $order->order_no }} — ZR Creation</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@500;600;700&family=Inter:wght@400;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Inter',sans-serif;font-size:13px;color:#111;background:#fff;padding:16px;}
  .ur{font-family:'Noto Nastaliq Urdu',serif;direction:rtl;line-height:2;}
  .num{font-family:'IBM Plex Mono',monospace;}
  h1{font-size:18px;margin-bottom:12px;border-bottom:2px solid #B0863A;padding-bottom:6px;color:#1B2A4A;}
  h1 b{color:#B0863A;}
  .grid{display:grid;grid-template-columns:180px 1fr 1fr 1fr;gap:0;border:1px solid #ccc;}
  .col{border-right:1px solid #ccc;padding:8px;}
  .col:last-child{border-right:none;}
  .col-head{font-weight:700;font-size:11px;text-transform:uppercase;color:#B0863A;letter-spacing:.4px;border-bottom:1px solid #ddd;padding-bottom:4px;margin-bottom:6px;}
  .row{display:flex;justify-content:space-between;padding:3px 0;border-bottom:1px dotted #ddd;font-size:12px;}
  .row:last-child{border-bottom:none;}
  .row .v{font-family:'IBM Plex Mono',monospace;font-weight:600;}
  .row .k{color:#444;}
  .chk{padding:3px 0;font-size:12px;}
  .chk .tick{display:inline-block;width:12px;color:#2F7A4D;font-weight:700;}
  @media print{body{padding:0;}}
</style>
</head>
<body>
<h1>ZR <b>Creation</b> — Order #{{ $order->order_no }}</h1>

<div class="grid">
  {{-- Customer --}}
  <div class="col">
    <div class="col-head">Customer</div>
    <div class="row"><span class="k">Name</span><span class="v">{{ $order->customer->name }}</span></div>
    <div class="row"><span class="k">S/O</span><span class="v">{{ $order->customer->reference ?? '—' }}</span></div>
    <div class="row"><span class="k">Phone</span><span class="v">{{ $order->customer->phone ?? '—' }}</span></div>
    <div class="row"><span class="k">Suit No</span><span class="v">{{ $order->order_no }}</span></div>
    <div class="row"><span class="k">Booking</span><span class="v">{{ $order->booking_date?->format('d/m/Y') ?? '—' }}</span></div>
    <div class="row"><span class="k">Delivery</span><span class="v">{{ $order->delivery_date?->format('d/m/Y') ?? '—' }}</span></div>
    <div class="row"><span class="k">Price</span><span class="v">{{ $order->price > 0 ? 'Rs.'.number_format($order->price,0) : '—' }}</span></div>
    <div class="row"><span class="k">Status</span><span class="v">{{ ucfirst($order->status) }}</span></div>
  </div>

  {{-- Kameez measurements --}}
  <div class="col">
    <div class="col-head">Kameez Size</div>
    @foreach($order->garments->firstWhere('garmentType.code','kameez')?->measurements->sortBy('measurementPoint.sort_order') ?? [] as $m)
      <div class="row">
        <span class="k ur">{{ $m->measurementPoint->name_ur }}</span>
        <span class="v">{{ $m->value ?? '—' }}</span>
      </div>
    @endforeach
  </div>

  {{-- Waistcoat measurements --}}
  <div class="col">
    <div class="col-head">Waistcoat Size</div>
    @foreach($order->garments->firstWhere('garmentType.code','waistcoat')?->measurements->sortBy('measurementPoint.sort_order') ?? [] as $m)
      <div class="row">
        <span class="k ur">{{ $m->measurementPoint->name_ur }}</span>
        <span class="v">{{ $m->value ?? '—' }}</span>
      </div>
    @endforeach
  </div>

  {{-- Design options --}}
  <div class="col">
    <div class="col-head">Design</div>
    @if($order->colour_note)
      <div class="row"><span class="k">Colour</span><span class="v">{{ $order->colour_note }}</span></div>
    @endif
    @foreach($order->designOptions->sortBy('sort_order') as $opt)
      <div class="chk">
        <span class="tick">✓</span>
        <span class="ur">{{ $opt->name_ur }}</span>
      </div>
    @endforeach
    @if($order->extra_notes)
      <div style="margin-top:6px;font-size:12px;color:#444;">{{ $order->extra_notes }}</div>
    @endif
  </div>
</div>

<script>window.onload=function(){window.print();}</script>
</body>
</html>
