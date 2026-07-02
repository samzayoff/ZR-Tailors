// ZR Creation — Tailor for Gents

// ── Mobile nav toggle (works on every page) ─────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('menuBtn');
    var nav = document.getElementById('nav');
    if (!btn || !nav) return;

    // Open / close the nav
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', nav.classList.contains('open'));
    });

    // Close when any nav link is clicked
    nav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            nav.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        });
    });

    // Close when clicking outside the header
    document.addEventListener('click', function (e) {
        if (!nav.contains(e.target) && e.target !== btn) {
            nav.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
        }
    });

    // Auto-hide flash toasts across all pages
    var flash = document.getElementById('flash-toast');
    if (flash) {
        setTimeout(function () { flash.classList.remove('show'); }, 2500);
    }
});
