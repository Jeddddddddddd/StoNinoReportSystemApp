<?php
// Strictly enforce resident or admin authorization
if (!session()->has('user') && !session()->has('admin')) {
    redirect('/login')->send();
    exit();
}

// Read-only: pulls the 3 newest updates for the "Latest from the Barangay" strip.
// Same table the Updates page uses; wrapped in try/catch so the homepage never breaks.
$latest = collect();
try {
    $latest = \Illuminate\Support\Facades\DB::table('barangay_updates')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
} catch (\Throwable $e) {
    $latest = collect();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Portal - Home</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/home-style.css') }}">
</head>
<body>

    <header class="navbar" id="navbar">
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

    <!-- ===================== HERO ===================== -->
    <section class="hero">
        <div class="hero-bg" style="background-image:url('{{ asset('assets/newbarangay.png') }}');"></div>
        <div class="hero-overlay"></div>
        <span class="hero-orb orb-1"></span>
        <span class="hero-orb orb-2"></span>

        <div class="hero-inner">
            <div class="hero-text">
                <span class="hero-eyebrow anim" style="--d:.1s">Official Community Portal</span>
                <h1 class="anim" style="--d:.25s">Your Barangay, <em>Closer to You</em></h1>
                <p class="hero-sub anim" style="--d:.4s">
                    Access community updates, view local announcements, or submit incident reports and
                    inquiries instantly online. Fast, transparent, and accessible to all residents.
                </p>

                <div class="hero-actions anim" style="--d:.55s">
                    <a href="{{ url('/report') }}" class="btn btn-primary"><span>Report an Issue</span></a>
                    <a href="{{ url('/updates') }}" class="btn btn-ghost">View Updates</a>
                </div>

            </div>

            <div class="hero-seal anim-seal">
                <span class="seal-glow"></span>
                <span class="seal-ring"></span>
                <div class="seal-disc">
                    <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Sto. Niño Seal">
                </div>
            </div>
        </div>

        <div class="hero-fade"></div>
    </section>

    <!-- ===================== QUICK ACTIONS ===================== -->
    <section class="quick-section">
        <div class="quick-grid">
            <a href="{{ url('/report') }}" class="quick-card reveal">
                <span class="quick-icon">📝</span>
                <h3>Report an Issue</h3>
                <p>Flooding, broken lights, noise, safety concerns, tell us and we'll act.</p>
                <span class="quick-link">File a report <b>&rarr;</b></span>
            </a>
            <a href="{{ url('/report') }}" class="quick-card reveal">
                <span class="quick-icon">💬</span>
                <h3>Send an Inquiry</h3>
                <p>Questions about clearances, certificates, or barangay services.</p>
                <span class="quick-link">Ask now <b>&rarr;</b></span>
            </a>
            <a href="{{ url('/updates') }}" class="quick-card reveal">
                <span class="quick-icon">📢</span>
                <h3>Community Updates</h3>
                <p>Announcements, programs, and project news from the barangay hall.</p>
                <span class="quick-link">See updates <b>&rarr;</b></span>
            </a>
            <a href="{{ url('/about') }}" class="quick-card reveal">
                <span class="quick-icon">🏛️</span>
                <h3>Meet Your Officials</h3>
                <p>Your captain, kagawad, tanod, and all 23 purok leaders.</p>
                <span class="quick-link">Get to know us <b>&rarr;</b></span>
            </a>
        </div>
    </section>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section class="section steps-section">
        <div class="section-head reveal">
            <span class="eyebrow">Simple Process</span>
            <h2>How Reporting Works</h2>
            <p>From your phone to the barangay hall in three easy steps.</p>
        </div>

        <div class="steps">
            <div class="step reveal">
                <span class="step-num">1</span>
                <h4>Submit your report</h4>
                <p>Describe the concern, add the location, and send it in under a minute.</p>
            </div>
            <div class="step reveal">
                <span class="step-num">2</span>
                <h4>Barangay reviews it</h4>
                <p>Your report goes straight to the responsible officials for action.</p>
            </div>
            <div class="step reveal">
                <span class="step-num">3</span>
                <h4>Get a response</h4>
                <p>Follow the progress and see it resolved no long lines, no guesswork.</p>
            </div>
        </div>
    </section>

    <!-- ===================== LATEST UPDATES ===================== -->
    <section class="section latest-section">
        <div class="section-head reveal">
            <span class="eyebrow">What's New</span>
            <h2>Latest from the Barangay</h2>
            <p>The newest announcements and events posted by the barangay hall.</p>
        </div>

        <div class="latest-grid">
            {{-- PREVIEW:LATEST --}}
            @forelse($latest as $row)
                <a href="{{ url('/updates') }}" class="mini-update reveal">
                    <div class="mini-img" @if(!empty($row->image_path)) style="background-image:url('{{ asset($row->image_path) }}')" @endif>
                        @if(empty($row->image_path)) <span>🏘️</span> @endif
                    </div>
                    <div class="mini-body">
                        <span class="mini-cat">{{ $row->category }}</span>
                        <h3>{{ $row->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($row->description, 110) }}</p>
                        <span class="mini-meta">Posted {{ date('M d, Y', strtotime($row->created_at)) }} <b>Read more &rarr;</b></span>
                    </div>
                </a>
            @empty
                <div class="mini-empty reveal">No updates posted yet — please check back soon.</div>
            @endforelse
            {{-- /PREVIEW:LATEST --}}
        </div>

        <div class="latest-more reveal">
            <a href="{{ url('/updates') }}" class="btn btn-outline">View all updates</a>
        </div>
    </section>

    <!-- ===================== HOTLINES ===================== -->
    <section class="hotline-section">
        <div class="hotline-inner">
            <div class="section-head light reveal">
                <span class="eyebrow">Need Help Right Away?</span>
                <h2>Emergency &amp; Helpdesk Lines</h2>
                <p>Keep these numbers handy. Replace the placeholders with your barangay's official lines.</p>
            </div>

            <div class="hotline-grid">
                <div class="hotline-card reveal">
                    <span class="hotline-icon">🚨</span>
                    <h4>National Emergency</h4>
                    <strong>911</strong>
                </div>
                <div class="hotline-card reveal">
                    <span class="hotline-icon">🛡️</span>
                    <h4>Barangay Tanod / Peace &amp; Order</h4>
                    <strong>0000-000-0000</strong>
                </div>
                <div class="hotline-card reveal">
                    <span class="hotline-icon">🩺</span>
                    <h4>Barangay Health Center</h4>
                    <strong>0000-000-0000</strong>
                </div>
                <div class="hotline-card reveal">
                    <span class="hotline-icon">🏛️</span>
                    <h4>Barangay Hall</h4>
                    <strong>0000-000-0000</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== CTA ===================== -->
    <section class="cta-section">
        <div class="cta-card reveal">
            <div>
                <h2>See something that needs attention?</h2>
                <p>Your report helps keep Sto. Niño safe, clean, and well cared for.</p>
            </div>
            <a href="{{ url('/report') }}" class="btn btn-dark"><span>Report an Issue</span></a>
        </div>
    </section>

    <script>
        const navbar = document.getElementById('navbar');
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');

        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('open');
            navToggle.classList.toggle('open');
            navbar.classList.toggle('menu-open');
        });

        // Navbar turns solid after scrolling
        const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 40);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        // Scroll reveal with gentle stagger inside each grid
        const groups = ['.quick-grid', '.steps', '.latest-grid', '.hotline-grid'];
        groups.forEach(sel => {
            document.querySelectorAll(sel + ' .reveal').forEach((el, i) => {
                el.style.transitionDelay = (i * 0.09) + 's';
            });
        });

        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('.reveal').forEach(el => io.observe(el));
    </script>

</body>
</html>