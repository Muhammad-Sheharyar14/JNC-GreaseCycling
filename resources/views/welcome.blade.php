<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Privacy Policy - JNC GreaseCycling</title>

    <!-- Google Fonts: Outfit for display & body (matches driver app) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Premium Stylesheet matching driver app colors -->
    <style>
        :root {
            --font-sans: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            
            /* Driver App Color Scheme */
            --bg-primary: #0a0e17;
            --bg-surface: rgba(20, 26, 38, 0.85);
            --bg-surface-elevated: rgba(30, 38, 56, 0.95);
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            
            --accent-color: #E04F26; /* Fox Orange */
            --accent-gradient: linear-gradient(135deg, #E04F26 0%, #B83A14 100%);
            
            --border-glass: rgba(255, 255, 255, 0.08);
            --shadow-premium: 0 10px 30px 0 rgba(0, 0, 0, 0.4);
            --shadow-glow: 0 0 25px rgba(224, 79, 38, 0.2);
        }

        /* Support standard light/dark modes but strictly preserve professional styling */
        @media (prefers-color-scheme: light) {
            :root {
                --bg-primary: #f8fafc;
                --bg-surface: #ffffff;
                --bg-surface-elevated: #f1f5f9;
                --text-primary: #0f172a;
                --text-secondary: #475569;
                --text-muted: #94a3b8;
                --border-glass: rgba(15, 23, 42, 0.08);
                --shadow-premium: 0 10px 30px 0 rgba(15, 23, 42, 0.06);
                --shadow-glow: 0 0 25px rgba(224, 79, 38, 0.12);
            }
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: var(--font-sans);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-image: radial-gradient(circle at top right, rgba(224, 79, 38, 0.06), transparent 45%),
                              radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.03), transparent 45%);
            background-attachment: fixed;
        }

        /* Full Screen Navigation Header */
        .header {
            position: sticky;
            top: 0;
            z-index: 100;
            background-color: rgba(10, 14, 23, 0.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            transition: all 0.3s ease;
        }

        @media (prefers-color-scheme: light) {
            .header {
                background-color: rgba(255, 255, 255, 0.95);
            }
        }

        .nav-container {
            max-width: 1200px; /* Aligned header max-width with content for a cohesive look */
            width: 100%;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 2rem;
        }

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        /* Professional Logo Badge Styling to handle JPG borders neatly in both modes */
        .brand-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            padding: 4px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .brand-logo-container:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .brand-logo {
            height: 60px;
            width: auto;
            object-fit: contain;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-secondary);
            transition: color 0.2s ease;
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

        .nav-link:hover, .nav-link.active {
            color: var(--text-primary);
        }

        .nav-link.active::after {
            width: 100%;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            color: #ffffff !important;
            background: var(--accent-gradient);
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(224, 79, 38, 0.25);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-login:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 6px 20px rgba(224, 79, 38, 0.4), var(--shadow-glow);
        }

        /* Hero Header Area */
        .hero {
            padding: 5rem 2rem 3rem 2rem;
            text-align: center;
            max-width: 1000px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.25rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 1.25rem;
            background: linear-gradient(to right, var(--text-primary), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (prefers-color-scheme: light) {
            .hero h1 {
                background: linear-gradient(to right, #0f172a, var(--accent-color));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
            font-weight: 400;
        }

        .meta-info {
            display: inline-block;
            margin-top: 1.5rem;
            font-size: 0.85rem;
            background-color: rgba(224, 79, 38, 0.1);
            color: var(--accent-color);
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            border: 1px solid rgba(224, 79, 38, 0.15);
        }

        /* Full page width reading layout (No sidebar) */
        .main-content {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto 8rem auto;
            padding: 0 2rem;
        }

        /* Card Section Content */
        .content-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 4rem;
            box-shadow: var(--shadow-premium);
        }

        .section {
            margin-bottom: 4rem;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid var(--border-glass);
            padding-bottom: 0.75rem;
        }

        .section-title svg {
            width: 1.75rem;
            height: 1.75rem;
            color: var(--accent-color);
            flex-shrink: 0;
        }

        .section p {
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .section ul {
            margin-left: 1.75rem;
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
        }

        .section li {
            margin-bottom: 0.75rem;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .section-highlight {
            background-color: rgba(224, 79, 38, 0.04);
            border-left: 4px solid var(--accent-color);
            padding: 1.5rem;
            border-radius: 0 12px 12px 0;
            margin: 2rem 0;
            border-top: 1px solid var(--border-glass);
            border-right: 1px solid var(--border-glass);
            border-bottom: 1px solid var(--border-glass);
        }

        .section-highlight p {
            margin-bottom: 0;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1.05rem;
        }

        /* Footer */
        .footer {
            background-color: rgba(10, 14, 23, 0.95);
            border-top: 1px solid var(--border-glass);
            padding: 4rem 2rem;
            text-align: center;
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        @media (prefers-color-scheme: light) {
            .footer {
                background-color: #ffffff;
            }
        }

        .footer-logo-container {
            display: inline-flex;
            background: #ffffff;
            padding: 4px;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .footer-logo-img {
            height: 46px;
            width: auto;
        }

        .footer p {
            margin-top: 0.5rem;
            font-weight: 400;
        }

        /* Fully Responsive System */
        @media (max-width: 1024px) {
            .main-content {
                padding: 0 1.5rem;
            }

            .content-card {
                padding: 2.5rem 1.75rem;
            }

            .hero h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .nav-container {
                padding: 0.85rem 1.5rem;
            }

            .nav-link {
                display: none; /* Keep mobile view minimal */
            }

            .hero {
                padding: 3.5rem 1.5rem 2rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .content-card {
                padding: 2rem 1.25rem;
            }

            .section-title {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <!-- Professional Navigation Header -->
    <header class="header">
        <div class="nav-container">
            <a href="/privacy-police" class="brand">
                <div class="brand-logo-container">
                    <img src="{{ asset('logo-1.png') }}" alt="JNC GreaseCycling Logo" class="brand-logo">
                </div>
            </a>
            
            <nav class="nav-menu">
                <a href="#privacy-policy" class="nav-link active">Privacy Policy</a>
                <a href="/driver/" class="btn-login">Driver Login</a>
            </nav>
        </div>
    </header>

    <!-- Header Introduction Section -->
    <section class="hero" id="privacy-policy">
        <h1>Privacy Policy</h1>
        <p>Your privacy and the security of your operational logistics data are of absolute importance to JNC GreaseCycling.</p>
        <span class="meta-info">Last Updated: July 2026</span>
    </section>

    <!-- Full Page Centered Reading Layout (No Sidebar) -->
    <main class="main-content">
        
        <div class="content-card">
            
            <!-- Section 1 -->
            <div class="section" id="section-1">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    1. Introduction
                </h2>
                <p>JNC GreaseCycling provides a specialized route management and grease collection platform. This Privacy Policy details the methods by which JNC GreaseCycling collects, processes, and protects operational information when users interact with the mobile driver application (the "App") or associated web platforms.</p>
            </div>

            <!-- Section 2 -->
            <div class="section" id="section-2">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    2. Location Tracking and Data Use
                </h2>
                <p>To enable efficient route optimization and verify restaurant grease collections, the App collects geographical location coordinates (latitude and longitude) from driver devices.</p>
                
                <div class="section-highlight">
                    <p>Background Location Access: Location data is collected even when the App is closed or not in use, specifically while drivers are actively completing an assigned route stop sequence. This ensures automated collection verification and accurate route logs.</p>
                </div>
                
                <p>Location data is used solely for the following business-critical workflows:</p>
                <ul>
                    <li>Guiding drivers to their designated customer sites.</li>
                    <li>Verifying physical arrivals and pickup events at restaurant client locations.</li>
                    <li>Optimizing route configurations and stop ordering to reduce transit duration and fuel consumption.</li>
                </ul>
            </div>

            <!-- Section 3 -->
            <div class="section" id="section-3">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    3. Information Collected
                </h2>
                <p>In addition to location telemetry, JNC GreaseCycling collects and processes the following data classes:</p>
                <ul>
                    <li><strong>Account Credentials:</strong> Name, phone number, and security hashes used strictly for authentication.</li>
                    <li><strong>Collection Details:</strong> Collected weight statistics, container profiles, stop completion statuses, skip reasons, and driver logs.</li>
                    <li><strong>App Diagnostics:</strong> Basic device parameters and performance metadata gathered to ensure system integrity.</li>
                </ul>
            </div>

            <!-- Section 4 -->
            <div class="section" id="section-4">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    4. Data Security and Systems Integrity
                </h2>
                <p>JNC GreaseCycling deploys robust technical and organizational security controls to safeguard all platform databases. All communication between the App and the server infrastructure is encrypted using Industry-standard Transport Layer Security (TLS/HTTPS). Operational archives are secured in compliance with corporate bookkeeping and regulatory retention obligations.</p>
            </div>

            <!-- Section 5 -->
            <div class="section" id="section-5">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    5. Account Deletion and User Rights
                </h2>
                <p>Authorized users may review their profile records, request updates to their data, or execute complete account deletion. To request deletion of a driver profile and its related logging histories, please submit a request to our systems administration department at support@jncgreasecycling.com.</p>
            </div>

            <!-- Section 6 -->
            <div class="section" id="section-6">
                <h2 class="section-title">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    6. Contact Information
                </h2>
                <p>For questions or concerns regarding this policy, please reach out to JNC GreaseCycling:</p>
                <ul>
                    <li>Email: support@jncgreasecycling.com</li>
                    <li>Address: JNC GreaseCycling, Attn: Logistics Compliance Department</li>
                </ul>
            </div>

        </div>
    </main>

    <!-- Professional Footer -->
    <footer class="footer">
        <div class="footer-logo-container">
            <img src="{{ asset('logo-1.png') }}" alt="JNC GreaseCycling Logo" class="footer-logo-img">
        </div>
        <p>&copy; 2026 JNC GreaseCycling. All rights reserved. Industrial Grease Collections & Route Logistics.</p>
    </footer>

</body>
</html>
