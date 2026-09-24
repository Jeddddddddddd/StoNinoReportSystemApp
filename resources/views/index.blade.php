<?php
// Strictly enforce resident or admin authorization
if (!session()->has('user') && !session()->has('admin')) {
    redirect('/login')->send();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Portal - Home</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <header class="navbar">
        <a href="{{ url('/') }}" class="nav-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Logo" class="brand-logo">
            Barangay<span>Sto.Niño</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-links" id="navLinks">
            <a href="{{ url('/') }}" class="active">Home</a>
            <a href="{{ url('/updates') }}">Updates</a>
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/report') }}">Report & Inquiries</a>
            <a href="{{ url('/logout') }}" class="mobile-logout">Log Out</a>
        </nav>

        <a href="{{ url('/logout') }}" class="admin-portal-btn desktop-logout">Log Out</a>
    </header>

    <section class="hero-section">
        <div class="hero-content">
            <span class="hero-subtitle">OFFICIAL COMMUNITY PORTAL</span>
            <h1>Your Barangay, <span class="highlight">Closer to You</span></h1>
            <p>Access community updates, view local announcements, or submit incident reports and inquiries instantly online. Fast, transparent, and accessible to all residents.</p>

            <div class="hero-buttons">
                <a href="{{ url('/report') }}" class="btn-primary">Report an Issue</a>
                <a href="{{ url('/updates') }}" class="btn-secondary">View Updates</a>
            </div>
        </div>

        <div class="hero-image-container">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Sto. Niño Logo" class="hero-img">
        </div>
    </section>

    <script>
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');
        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            navToggle.classList.toggle('open');
        });
    </script>

</body>
</html>