<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>JNC GreaseCycling - Premium Grease Recycling & Fleet Logistics</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Premium Stylesheet with Sleek Dark Theme & Fox Orange Accent -->
    <style>
        :root {
            --font-sans: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            
            /* Sleek Dark Palette matching corporate fleet aesthetics */
            --bg-primary: #0a0e17;
            --bg-surface: rgba(20, 26, 38, 0.65);
            --bg-surface-elevated: rgba(30, 38, 56, 0.9);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            
            --accent-color: #E04F26; /* Fox Orange */
            --accent-gradient: linear-gradient(135deg, #E04F26 0%, #B83A14 100%);
            --accent-glow: rgba(224, 79, 38, 0.2);
            
            --border-glass: rgba(255, 255, 255, 0.08);
            --shadow-premium: 0 20px 40px 0 rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 30px rgba(224, 79, 38, 0.25);
            
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @media (prefers-color-scheme: light) {
            :root {
                --bg-primary: #f8fafc;
                --bg-surface: rgba(255, 255, 255, 0.8);
                --bg-surface-elevated: #ffffff;
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-muted: #94a3b8;
                --border-glass: rgba(15, 23, 42, 0.08);
                --shadow-premium: 0 20px 40px 0 rgba(15, 23, 42, 0.06);
                --shadow-glow: 0 0 30px rgba(224, 79, 38, 0.12);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at top right, rgba(224, 79, 38, 0.08), transparent 45%),
                              radial-gradient(circle at center left, rgba(16, 185, 129, 0.03), transparent 45%);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        /* Container helper */
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Navigation Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: rgba(10, 14, 23, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-glass);
            transition: var(--transition-smooth);
        }

        @media (prefers-color-scheme: light) {
            .header {
                background-color: rgba(255, 255, 255, 0.9);
            }
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 0;
        }

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .brand-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 5px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition-smooth);
        }

        .brand-logo-container:hover {
            transform: scale(1.03);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }

        .brand-logo {
            height: 55px;
            width: auto;
            object-fit: contain;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2.2rem;
        }

        .nav-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-secondary);
            transition: var(--transition-smooth);
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 0;
            height: 2.5px;
            background: var(--accent-gradient);
            border-radius: 99px;
            transition: width 0.25s ease;
        }

        .nav-link:hover {
            color: var(--text-primary);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            color: #ffffff !important;
            background: var(--accent-gradient);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(224, 79, 38, 0.3);
            transition: var(--transition-smooth);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(224, 79, 38, 0.45), var(--shadow-glow);
        }

        /* Hero Section */
        .hero-section {
            padding: 7.5rem 0 5rem 0;
            position: relative;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3.75rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text-primary) 30%, var(--accent-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (prefers-color-scheme: light) {
            .hero-content h1 {
                background: linear-gradient(135deg, #0f172a 30%, var(--accent-color) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        }

        .hero-content p {
            font-size: 1.25rem;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            max-width: 600px;
            font-weight: 400;
            line-height: 1.7;
        }

        .cta-group {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 2rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            color: #ffffff;
            background: var(--accent-gradient);
            box-shadow: 0 4px 14px rgba(224, 79, 38, 0.35);
            transition: var(--transition-smooth);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(224, 79, 38, 0.5), var(--shadow-glow);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.85rem 2rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.05rem;
            text-decoration: none;
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-glass);
            transition: var(--transition-smooth);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            transform: translateY(-2px);
        }

        /* Hero Image/Graphic */
        .hero-graphic {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-card {
            background: var(--bg-surface-elevated);
            border: 1px solid var(--border-glass);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow-premium);
            position: relative;
            z-index: 2;
            backdrop-filter: blur(12px);
        }

        /* Glow effect in background of graphic */
        .hero-graphic::after {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, var(--accent-color) 0%, transparent 70%);
            opacity: 0.15;
            z-index: 1;
            filter: blur(40px);
        }

        .hero-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .hero-card-stat {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--accent-color);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .hero-card-desc {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Stats Grid Section */
        .stats-section {
            padding: 3rem 0;
            border-top: 1px solid var(--border-glass);
            border-bottom: 1px solid var(--border-glass);
            background: rgba(255, 255, 255, 0.01);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }

        .stat-item h4 {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .stat-item p {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Features Section */
        .section-padding {
            padding: 8rem 0;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 5rem auto;
        }

        .section-header h2 {
            font-size: 2.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .section-header p {
            font-size: 1.15rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
        }

        .feature-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 3rem 2.25rem;
            box-shadow: var(--shadow-premium);
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(224, 79, 38, 0.3);
            background: var(--bg-surface-elevated);
        }

        .feature-icon-container {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 10px;
            background: rgba(224, 79, 38, 0.1);
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
            border: 1px solid rgba(224, 79, 38, 0.15);
        }

        .feature-icon-container svg {
            width: 1.85rem;
            height: 1.85rem;
        }

        .feature-card h3 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .feature-card p {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* How It Works Section */
        .process-section {
            background: rgba(224, 79, 38, 0.02);
            border-top: 1px solid var(--border-glass);
            border-bottom: 1px solid var(--border-glass);
        }

        .process-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3rem;
            position: relative;
        }

        .process-step {
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            background: var(--accent-gradient);
            color: #ffffff;
            font-size: 1.35rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem auto;
            box-shadow: 0 4px 10px rgba(224, 79, 38, 0.3);
        }

        .process-step h3 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .process-step p {
            font-size: 1rem;
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 300px;
            margin: 0 auto;
        }

        /* Driver CTA Banner Card */
        .driver-cta-section {
            padding: 2rem 0 8rem 0;
        }

        .driver-banner {
            background: linear-gradient(135deg, rgba(20, 26, 38, 0.9) 0%, rgba(10, 14, 23, 0.95) 100%);
            border: 1px solid rgba(224, 79, 38, 0.2);
            border-radius: 24px;
            padding: 4.5rem;
            box-shadow: var(--shadow-premium), var(--shadow-glow);
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .driver-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(224, 79, 38, 0.15) 0%, transparent 70%);
            z-index: 1;
            pointer-events: none;
        }

        .driver-banner-content {
            position: relative;
            z-index: 2;
        }

        .driver-banner-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--text-primary);
            letter-spacing: -0.02em;
        }

        .driver-banner-content p {
            font-size: 1.15rem;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .driver-banner-action {
            display: flex;
            justify-content: flex-end;
            position: relative;
            z-index: 2;
        }

        .btn-driver-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2.5rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.15rem;
            text-decoration: none;
            color: #ffffff;
            background: var(--accent-gradient);
            box-shadow: 0 4px 15px rgba(224, 79, 38, 0.4);
            transition: var(--transition-smooth);
            gap: 0.75rem;
        }

        .btn-driver-login svg {
            width: 1.35rem;
            height: 1.35rem;
        }

        .btn-driver-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(224, 79, 38, 0.6), var(--shadow-glow);
        }

        /* Footer */
        .footer {
            background-color: rgba(10, 14, 23, 0.98);
            border-top: 1px solid var(--border-glass);
            padding: 5rem 0 3rem 0;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        @media (prefers-color-scheme: light) {
            .footer {
                background-color: #ffffff;
            }
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 4rem;
            align-items: center;
            margin-bottom: 3rem;
        }

        .footer-info p {
            margin-top: 1rem;
            max-width: 400px;
            line-height: 1.6;
        }

        .footer-links {
            display: flex;
            gap: 3rem;
            justify-content: flex-end;
        }

        .footer-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .footer-links a:hover {
            color: var(--accent-color);
        }

        .footer-bottom {
            border-top: 1px solid var(--border-glass);
            padding-top: 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        /* Fully Responsive Grid and Layout */
        @media (max-width: 1024px) {
            .hero-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 3rem;
            }

            .hero-content p {
                margin: 0 auto 2.5rem auto;
            }

            .cta-group {
                justify-content: center;
            }

            .hero-graphic {
                /* Let natural DOM order flow: content first, graphic second in mobile view */
            }

            .features-grid {
                grid-template-columns: 1fr;
                max-width: 600px;
                margin: 0 auto;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 3rem;
            }

            .process-grid {
                grid-template-columns: 1fr;
                gap: 4rem;
            }

            .driver-banner {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2.5rem;
                padding: 3rem;
            }

            .driver-banner-action {
                justify-content: center;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2.5rem;
            }

            .footer-links {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            body {
                background-image: radial-gradient(circle at top right, rgba(224, 79, 38, 0.05), transparent 60%);
            }

            .nav-link {
                display: none;
            }

            .hero-content h1 {
                font-size: 2.75rem;
            }

            .section-padding {
                padding: 5rem 0;
            }

            .section-header h2 {
                font-size: 2.2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
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
                <a href="#services" class="nav-link">Our Services</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="/privacy-police" class="nav-link">Privacy Policy</a>
                <a href="/driver/" class="btn-login">Login</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container hero-grid">
            <div class="hero-content">
                <h1>Reliable Grease Recycling & Field Operations</h1>
                <p>JNC GreaseCycling provides premium cooking oil recycling, automated container pickup scheduling, and high-yield payouts for commercial kitchens across the region.</p>
                <div class="cta-group">
                    <a href="#how-it-works" class="btn-primary">Learn How It Works</a>
                    <a href="/driver/" class="btn-secondary">Driver Portal</a>
                </div>
            </div>
            
            <div class="hero-graphic">
                <div class="hero-card">
                    <h3>Commercial Impact</h3>
                    <div class="hero-card-stat">500k+</div>
                    <div class="hero-card-desc">Pounds of used cooking oil successfully recycled into clean biofuel.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Grid Section -->
    <section class="stats-section">
        <div class="container stats-grid">
            <div class="stat-item">
                <h4>99.8%</h4>
                <p>On-Time Collection</p>
            </div>
            <div class="stat-item">
                <h4>24/7</h4>
                <p>Fleet Dispatch</p>
            </div>
            <div class="stat-item">
                <h4>Top-Tier</h4>
                <p>Reimbursement Rates</p>
            </div>
            <div class="stat-item">
                <h4>100%</h4>
                <p>Eco-Friendly Process</p>
            </div>
        </div>
    </section>

    <!-- Features/Services Section -->
    <section class="section-padding" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Operations & Services</h2>
                <p>We provide full-service, hassle-free logistics designed to keep your kitchen clean, compliant, and earning revenue.</p>
            </div>
            
            <div class="features-grid">
                <!-- Service 1 -->
                <div class="feature-card">
                    <div class="feature-icon-container">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <h3>Zero-Spill Collection</h3>
                    <p>We provide commercial-grade oil storage containers, complete scheduled pickups, and use clean vacuum extraction to ensure zero spills.</p>
                </div>
                
                <!-- Service 2 -->
                <div class="feature-card">
                    <div class="feature-icon-container">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3>Smart Fleet Dispatching</h3>
                    <p>Our backend routing dynamically schedules pickup stops according to your container volume, avoiding overflow and reducing transit time.</p>
                </div>
                
                <!-- Service 3 -->
                <div class="feature-card">
                    <div class="feature-icon-container">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3>High-Yield Oil Payouts</h3>
                    <p>Every pound of grease collected is digitally logged by our drivers. Restaurant partners receive competitive payouts calculated on precise volumes.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="section-padding process-section" id="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>Seamless Service Process</h2>
                <p>We work in three simple stages to integrate our collection operations with your business operations.</p>
            </div>
            
            <div class="process-grid">
                <!-- Step 1 -->
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h3>Container Placement</h3>
                    <p>We set up a secure, heavy-duty collection container at your restaurant location at no cost to you.</p>
                </div>
                
                <!-- Step 2 -->
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h3>Dynamic Scheduling</h3>
                    <p>Our fleet scheduler generates route stops and assigns our drivers to empty your containers on time.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h3>Verified Payouts</h3>
                    <p>Drivers weigh collections on-site, log them directly into our system, and we transfer your earnings.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Driver Fleet Section -->
    <section class="driver-cta-section">
        <div class="container">
            <div class="driver-banner">
                <div class="driver-banner-content">
                    <h2>Fleet Operations Portal</h2>
                    <p>Are you a JNC GreaseCycling field driver? Access your daily scheduled routes, log stop completions, check restaurant notes, and coordinate collection runs in real time.</p>
                </div>
                <div class="driver-banner-action">
                    <a href="/driver/" class="btn-driver-login">
                        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-6 6m0 0l-6-6m6 6V9a9 9 0 0118 0v12"></path>
                        </svg>
                        Driver Portal Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
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
                    <a href="#services">Services</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="/privacy-police">Privacy Policy</a>
                    <a href="/driver/">Driver Portal</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 JNC GreaseCycling. All rights reserved. Industrial Grease Collections & Route Logistics.</p>
            </div>
        </div>
    </footer>

</body>
</html>
