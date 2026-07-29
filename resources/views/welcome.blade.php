@extends('layouts.app')

@section('title', 'JNC GreaseCycling - Premium Grease Recycling & Fleet Logistics')

@section('content')
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
@endsection
