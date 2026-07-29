@extends('layouts.app')

@section('title', 'Privacy Policy - JNC GreaseCycling')

@section('content')
    <!-- Header Introduction Section -->
    <section class="hero" id="privacy-policy">
        <h1>Privacy Policy</h1>
        <p>Your privacy and the security of your operational logistics data are of absolute importance to JNC GreaseCycling.</p>
        <span class="meta-info">Last Updated: July 2026</span>
    </section>

    <!-- Full Page Centered Reading Layout (No Sidebar) -->
    <div class="main-content">
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
    </div>
@endsection
