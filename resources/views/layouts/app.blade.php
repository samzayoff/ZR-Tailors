<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ZR Creation — Tailor for Gents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&family=Noto+Nastaliq+Urdu:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body>

    {{-- ===== ICON LIBRARY ===== --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <g id="i-len" viewBox="0 0 32 32">
                <path d="M16 4v24M12 7l4-3 4 3M12 25l4 3 4-3M10 10h4M10 16h4M10 22h4" fill="none"
                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </g>
            <g id="i-shoulder">
                <path d="M4 14c4-5 8-7 12-7s8 2 12 7M16 7V4M5 14l1 2M27 14l-1 2" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </g>
            <g id="i-sleeve">
                <path d="M11 5h6v8l5 11-5 3-4-9-2-4z" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linejoin="round" />
                <path d="M11 9h6" stroke="currentColor" stroke-width="1.4" />
            </g>
            <g id="i-chest">
                <path d="M12 5L5 9l3 4 3-1v15h12V12l3 1 3-4-7-4-3 3-3-3z" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linejoin="round" />
                <path d="M11 16h10" stroke="currentColor" stroke-width="1.4" stroke-dasharray="2 2" />
            </g>
            <g id="i-waist">
                <rect x="4" y="13" width="24" height="6" rx="2" fill="none" stroke="currentColor"
                    stroke-width="1.7" />
                <rect x="13" y="12" width="6" height="8" rx="1" fill="none" stroke="currentColor"
                    stroke-width="1.7" />
            </g>
            <g id="i-daman">
                <path d="M9 6h14l4 20H5z" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linejoin="round" />
                <path d="M5 26h22" stroke="currentColor" stroke-width="1.4" stroke-dasharray="2 2" />
            </g>
            <g id="i-collar">
                <path d="M16 6l-7 5 4 4 3-3 3 3 4-4z" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linejoin="round" />
            </g>
            <g id="i-shalwar">
                <path d="M8 4h16l-2 24h-5l-1-14-1 14H10z" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linejoin="round" />
            </g>
            <g id="i-pancha">
                <rect x="10" y="7" width="12" height="18" rx="2" fill="none" stroke="currentColor"
                    stroke-width="1.7" />
                <path d="M10 12h12" stroke="currentColor" stroke-width="1.4" />
            </g>
            <g id="i-bais">
                <path d="M12 4v24M20 4v24M12 9h8M12 23h8" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linecap="round" />
            </g>
            <g id="i-kameez">
                <path d="M12 4L5 8l3 5 3-1v16h12V12l3 1 3-5-7-4-3 3-3-3z" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linejoin="round" />
            </g>
            <g id="i-vest">
                <path d="M11 5l5 5 5-5 6 4v18H5V9z" fill="none" stroke="currentColor" stroke-width="1.7"
                    stroke-linejoin="round" />
                <path d="M16 10v9" stroke="currentColor" stroke-width="1.4" />
            </g>
            <g id="i-cuff">
                <rect x="8" y="9" width="16" height="14" rx="2" fill="none" stroke="currentColor"
                    stroke-width="1.7" />
                <path d="M12 9v14M20 9v14" stroke="currentColor" stroke-width="1.4" />
            </g>
            <g id="i-pen">
                <path d="M5 27l4-1L25 10l-3-3L6 23l-1 4zM20 9l3 3" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linejoin="round" />
            </g>
            <g id="i-needle">
                <path d="M6 26L21 11M23 9l-4 .5M23 9l-.5 4M22.5 5.5a3 3 0 1 0 .01 0" fill="none"
                    stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
            </g>
        </defs>
    </svg>

    {{-- ===== HEADER ===== --}}
    <header>
        <div class="topbar">
            <div class="brand">
                <span class="mark">ZR <b>Creation</b></span>
                <span class="sub">Tailor for Gents</span>
            </div>
            <button class="menu-btn" id="menuBtn" aria-label="Open menu">&#9776;</button>
            <nav id="nav">
                <a href="{{ route('dashboard.index') }}"
                    class="key {{ request()->routeIs('dashboard.index') ? 'is-active' : '' }}">Dashboard</a>

                <a href="{{ route('report.index') }}"
                    class="key {{ request()->routeIs('report.index') ? 'is-active' : '' }}">Report</a>

                <div class="dropdown">
                    <button
                        class="key dropdown-btn {{ request()->routeIs('orders.index') || request()->routeIs('orders.updateOrder') ? 'is-active' : '' }}">
                        Order ▾
                    </button>

                    <div class="dropdown-content">
                        <a href="{{ route('orders.index') }}"
                            class="{{ request()->routeIs('orders.index') ? 'is-active' : '' }}">
                            New Order
                        </a>

                        <a href="{{ route('orders.updateOrder') }}"
                            class="{{ request()->routeIs('orders.updateOrder') ? 'is-active' : '' }}">
                            Update Order
                        </a>
                    </div>
                </div>

                <div class="dropdown">
                    <button
                        class="key dropdown-btn {{ request()->routeIs('orders.searchOrder') || request()->routeIs('customers.index') ? 'is-active' : '' }}">
                        Customers ▾
                    </button>

                    <div class="dropdown-content">
                        <a href="{{ route('orders.searchOrder') }}"
                            class="key {{ request()->routeIs('orders.searchOrder') ? 'is-active' : '' }}">Suits Per
                            Customer</a>

                        <a href="{{ route('customers.index') }}"
                            class="key {{ request()->routeIs('customers.index') ? 'is-active' : '' }}">Customers</a>
                    </div>
                </div>
                {{-- <a href="#" onclick="window.print();return false;">Print</a> --}}




            </nav>
        </div>
    </header>

    {{-- ===== FLASH MESSAGES ===== --}}
    @if (session('success'))
        <div id="flash-toast" class="flash-toast show">{{ session('success') }}</div>
    @endif

    {{-- ===== MAIN CONTENT ===== --}}
    @yield('content')

    {{-- ===== TOAST ===== --}}
    <div id="toast"><span id="toastMsg">Saved</span></div>

    {{-- ===== FOOTER ===== --}}
    <footer class="site-footer">
        <div class="footer-inner">
            &copy; {{ date('Y') }} <b>ZR Creation</b> — Tailor for Gents. All rights reserved.
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>
