<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'JNC GreaseCycling - Premium Grease Recycling & Fleet Logistics')</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Premium External Stylesheet & JS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('styles')
</head>
<body>

    <!-- Professional Navigation Header -->
    <header class="header">
        <div class="container nav-container">
            <a href="/" class="brand">
                <div class="brand-logo-container">
                    <img src="{{ asset('logo-1.png') }}" alt="JNC GreaseCycling Logo" class="brand-logo">
                </div>
            </a>
            
            <nav class="nav-menu">
                <a href="{{ request()->is('/') ? '#services' : '/#services' }}" class="nav-link">Our Services</a>
                <a href="{{ request()->is('/') ? '#how-it-works' : '/#how-it-works' }}" class="nav-link">How It Works</a>
                <a href="/privacy-police" class="nav-link {{ request()->is('privacy-police') ? 'active' : '' }}">Privacy Policy</a>
                <a href="/driver/" class="btn-login">Login</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Unified Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <div class="brand-logo-container" style="display: inline-flex;">
                        <img src="{{ asset('logo-1.png') }}" alt="JNC GreaseCycling Logo" class="brand-logo" style="height: 48px;">
                    </div>
                    <p>Premium operational routing and green energy recycling services. Converting commercial kitchen waste into clean bio-energy solutions.</p>
                </div>
                <div class="footer-links">
                    <a href="{{ request()->is('/') ? '#services' : '/#services' }}">Services</a>
                    <a href="{{ request()->is('/') ? '#how-it-works' : '/#how-it-works' }}">How It Works</a>
                    <a href="/privacy-police">Privacy Policy</a>
                    <a href="/driver/">Driver Portal</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 JNC GreaseCycling. All rights reserved. Industrial Grease Collections & Route Logistics.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
