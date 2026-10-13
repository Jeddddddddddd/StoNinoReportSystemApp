<?php
use Illuminate\Support\Facades\DB;

// Strictly enforce user or admin session authorization
if (!session()->has('user') && !session()->has('admin')) {
    redirect('/login')->send();
    exit();
}

// Fetch all updates from the database using Laravel Query Builder, newest first
$updates = DB::table('barangay_updates')->orderBy('created_at', 'desc')->get();

/**
 * Pure display helper — maps a category label to a color theme class.
 * Does NOT touch the database; purely cosmetic categorization for the UI.
 */
function updateCategoryClass($category) {
    $c = strtolower(trim($category ?? ''));

    if (str_contains($c, 'alert') || str_contains($c, 'advisory') || str_contains($c, 'warning')) {
        return 'cat-alert';
    }
    if (str_contains($c, 'health') || str_contains($c, 'medical') || str_contains($c, 'vaccin')) {
        return 'cat-health';
    }
    if (str_contains($c, 'project') || str_contains($c, 'infrastructure') || str_contains($c, 'repair') || str_contains($c, 'construction')) {
        return 'cat-project';
    }
    if (str_contains($c, 'event') || str_contains($c, 'fiesta') || str_contains($c, 'program') || str_contains($c, 'gym') || str_contains($c, 'batch')) {
        return 'cat-event';
    }
    if (str_contains($c, 'announce') || str_contains($c, 'notice') || str_contains($c, 'update')) {
        return 'cat-announcement';
    }
    return 'cat-default';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Updates & Events - Sto.Niño</title>
    @include('partials.head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/update-style.css') }}">
    <noscript><style>.reveal { opacity: 1 !important; }</style></noscript>
</head>
<body>

    {{-- Icon sprite (decorative; used by the category chips, date badges and empty states) --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
        <defs>
            <symbol id="i-event" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>
            </symbol>
            <symbol id="i-announcement" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10v4h3l6 4V6L6 10z"/><path d="M15.5 9.5a3.5 3.5 0 0 1 0 5M18.5 7a7 7 0 0 1 0 10"/>
            </symbol>
            <symbol id="i-project" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </symbol>
            <symbol id="i-alert" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </symbol>
            <symbol id="i-health" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z"/>
            </symbol>
            <symbol id="i-default" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>
            </symbol>
            <symbol id="i-house" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>
            </symbol>
            <symbol id="i-inbox" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>
            </symbol>
        </defs>
    </svg>

    <header class="navbar">
        <a href="{{ url('/') }}" class="nav-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Logo" class="brand-logo">
            Barangay<span>Sto.Niño</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="nav-links" id="navLinks">
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('/updates') }}" class="active">Updates</a>
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/report') }}">Report & Inquiries</a>
            <a href="{{ url('/logout') }}" class="mobile-logout">Log Out</a>
        </nav>

        <a href="{{ url('/logout') }}" class="admin-portal-btn desktop-logout">Log Out</a>
    </header>

    <div class="updates-container">
        <div class="updates-header">
            <span class="updates-eyebrow hero-in" style="--i:0">
                <svg class="broadcast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10v4h3l6 4V6L6 10z"/>
                    <path class="wave wave-1" d="M15.5 9.5a3.5 3.5 0 0 1 0 5"/>
                    <path class="wave wave-2" d="M18 7.5a6.5 6.5 0 0 1 0 9"/>
                    <path class="wave wave-3" d="M20.5 5.5a9.5 9.5 0 0 1 0 13"/>
                </svg>
                Barangay Portal
            </span>
            <h1 class="hero-in" style="--i:1">Community Updates & Events</h1>
            <p class="hero-in" style="--i:2">Stay informed on upcoming projects, community repairs, programs, and announcements from Barangay Sto. Niño.</p>

            @if ($updates->count() > 0)
                <div class="updates-meta-bar hero-in" style="--i:3">
                    <span class="updates-count-pill"><span class="live-dot" aria-hidden="true"></span><span><span data-count="{{ $updates->count() }}">{{ $updates->count() }}</span> {{ $updates->count() === 1 ? 'Update' : 'Updates' }} Posted</span></span>
                    <span class="updates-hint">Click any update to view full details</span>
                </div>
            @endif

            {{-- Decorative barangay skyline: hall, houses and trees draw themselves, the flag waves, clouds drift --}}
            <svg class="skyline" viewBox="0 0 800 110" aria-hidden="true" focusable="false">
                <g class="cloud-wrap" transform="translate(150 26)"><g class="cloud">
                    <ellipse cx="0" cy="0" rx="26" ry="7"/><ellipse cx="-9" cy="-6" rx="12" ry="8"/><ellipse cx="9" cy="-8" rx="14" ry="10"/>
                </g></g>
                <g class="cloud-wrap" transform="translate(640 34)"><g class="cloud cloud-b">
                    <ellipse cx="0" cy="0" rx="22" ry="6"/><ellipse cx="-7" cy="-5" rx="10" ry="7"/><ellipse cx="8" cy="-7" rx="12" ry="8"/>
                </g></g>

                <path class="draw ground" style="--k:0" pathLength="1" d="M10 100H790"/>

                <!-- house A + tree -->
                <path class="draw" style="--k:1" pathLength="1" d="M52 100V72H112V100"/>
                <path class="draw" style="--k:2" pathLength="1" d="M44 74L82 48L120 74"/>
                <path class="draw" style="--k:3" pathLength="1" d="M74 100V84H90V100"/>
                <path class="draw" style="--k:3" pathLength="1" d="M146 100V84M132 72a14 14 0 1 0 28 0a14 14 0 1 0 -28 0"/>

                <!-- house B -->
                <path class="draw" style="--k:4" pathLength="1" d="M190 100V68H252V100"/>
                <path class="draw" style="--k:5" pathLength="1" d="M182 70L221 42L260 70"/>
                <path class="draw" style="--k:6" pathLength="1" d="M201 78h14v12h-14zM227 78h14v12h-14z"/>

                <!-- barangay hall -->
                <path class="draw" style="--k:6" pathLength="1" d="M318 100H482"/>
                <path class="draw" style="--k:7" pathLength="1" d="M334 100V60H466V100"/>
                <path class="draw" style="--k:8" pathLength="1" d="M326 60L400 30L474 60Z"/>
                <path class="draw" style="--k:9" pathLength="1" d="M356 60V100M380 60V100M420 60V100M444 60V100"/>
                <path class="draw" style="--k:10" pathLength="1" d="M390 100V80H410V100"/>
                <path class="draw" style="--k:10" pathLength="1" d="M400 30V6"/>
                <g transform="translate(400 6)"><path class="flag" d="M0 0h24l-6 6l6 6H0z"/></g>

                <!-- tree + house C -->
                <path class="draw" style="--k:9" pathLength="1" d="M526 100V84M512 72a14 14 0 1 0 28 0a14 14 0 1 0 -28 0"/>
                <path class="draw" style="--k:10" pathLength="1" d="M566 100V70H632V100"/>
                <path class="draw" style="--k:11" pathLength="1" d="M558 72L599 44L640 72"/>
                <path class="draw" style="--k:12" pathLength="1" d="M591 100V86H607V100"/>

                <!-- house D + tree -->
                <path class="draw" style="--k:12" pathLength="1" d="M676 100V78H730V100"/>
                <path class="draw" style="--k:13" pathLength="1" d="M668 80L703 56L738 80"/>
                <path class="draw" style="--k:13" pathLength="1" d="M766 100V86M754 76a12 12 0 1 0 24 0a12 12 0 1 0 -24 0"/>
            </svg>
        </div>

        <div class="cards-list">
            @if ($updates->count() > 0)
                @foreach($updates as $index => $row)
                    <?php
                        $has_image = !empty($row->image_path);
                        $event_date_raw = !empty($row->event_date) ? date('F d, Y', strtotime($row->event_date)) : '';
                        $posted_raw = date('M d, Y', strtotime($row->created_at));
                        $catClass = updateCategoryClass($row->category);
                        $indexNum = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                        // display-only helpers for the new UI
                        $iconKey = str_replace('cat-', '', $catClass);
                        $isNew = strtotime($row->created_at) >= strtotime('-3 days');
                        $catLabel = !empty($row->category) ? $row->category : 'Update';
                    ?>
                    <div class="update-card reveal {{ $catClass }}"
                         tabindex="0"
                         role="button"
                         aria-haspopup="dialog"
                         data-title="{{ $row->title }}"
                         data-category="{{ $catLabel }}"
                         data-cat-class="{{ $catClass }}"
                         data-icon="{{ $iconKey }}"
                         data-description="{{ $row->description }}"
                         data-image="{{ $has_image ? asset($row->image_path) : '' }}"
                         data-event-date="{{ $event_date_raw }}"
                         data-posted="{{ $posted_raw }}">

                        <div class="update-img-wrapper">
                            @if ($has_image)
                                <img src="{{ asset($row->image_path) }}" alt="Update Image" class="update-img" loading="lazy">
                            @else
                                <div class="update-placeholder">
                                    <svg class="placeholder-icon" aria-hidden="true"><use href="#i-house"/></svg>
                                    <span>No image provided</span>
                                </div>
                            @endif
                            @if ($isNew)
                                <span class="new-badge"><span class="new-dot" aria-hidden="true"></span>New</span>
                            @endif
                        </div>

                        <div class="update-content">
                            <div class="update-content-top">
                                <div class="update-topline">
                                    <span class="update-category"><svg class="cat-icon" aria-hidden="true"><use href="#i-{{ $iconKey }}"/></svg>{{ $catLabel }}</span>
                                    <span class="update-index">No. {{ $indexNum }}</span>
                                </div>
                                <h3>{{ $row->title }}</h3>
                                <p>{!! nl2br(e($row->description)) !!}</p>

                                @if (!empty($event_date_raw))
                                    <div class="update-when">
                                        <span class="when-icon"><svg class="ico" aria-hidden="true"><use href="#i-event"/></svg></span>
                                        <span>
                                            <small>Event date</small>
                                            <strong>{{ $event_date_raw }}</strong>
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div class="update-footer">
                                <span class="posted-date"><span class="dot-icon"></span>Posted on {{ $posted_raw }}</span>
                                <span class="view-details-link">View details <span class="arrow-circle"><span class="arrow">&rarr;</span></span></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-updates reveal">
                    <svg class="no-updates-icon" aria-hidden="true"><use href="#i-inbox"/></svg>
                    <h3>No updates available right now.</h3>
                    <p>Check back later for upcoming barangay events and announcements.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="modal-overlay" id="updateModalOverlay">
        <div class="modal-box" id="updateModalBox" role="dialog" aria-modal="true" aria-labelledby="updateModalTitle">
            <button type="button" class="modal-close" id="updateModalCloseBtn" aria-label="Close">&times;</button>

            <div class="modal-img-wrapper m-img" id="updateModalImageWrapper">
                <img id="updateModalImage" alt="Update image">
                <div class="modal-img-fallback" id="updateModalImageFallback">
                    <svg class="ico" aria-hidden="true"><use href="#i-house"/></svg>
                </div>
            </div>

            <div class="modal-details-panel" id="updateModalPanel">
                <div class="modal-details-scroll">
                    <span class="update-category m-item" style="--i:0">
                        <svg class="cat-icon" aria-hidden="true"><use id="updateModalCatIcon" href="#i-default"/></svg>
                        <span id="updateModalCategory"></span>
                    </span>
                    <h3 id="updateModalTitle" class="m-item" style="--i:1"></h3>

                    <div class="modal-facts m-item" style="--i:2">
                        <div class="fact-tile" id="updateModalEventDate" style="display:none;">
                            <span class="fact-icon"><svg class="ico" aria-hidden="true"><use href="#i-event"/></svg></span>
                            <span>
                                <span class="fact-label">Event date</span>
                                <strong id="updateModalEventDateText"></strong>
                            </span>
                        </div>
                        <div class="fact-tile" id="updateModalPostedTile" style="display:none;">
                            <span class="fact-icon"><svg class="ico" aria-hidden="true"><use href="#i-announcement"/></svg></span>
                            <span>
                                <span class="fact-label">Posted on</span>
                                <strong id="updateModalPosted"></strong>
                            </span>
                        </div>
                    </div>

                    <div class="modal-divider m-item" style="--i:3"></div>

                    <div class="modal-section-label m-item" style="--i:4">About this update</div>
                    <p id="updateModalDescription" class="modal-full-description m-item" style="--i:5"></p>

                    <div class="modal-actions m-item" style="--i:6">
                        <button type="button" class="modal-done" id="updateModalDoneBtn">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // --- Mobile nav toggle ---
            const navToggle = document.getElementById('navToggle');
            const navLinks = document.getElementById('navLinks');
            navToggle.addEventListener('click', () => {
                const open = navLinks.classList.toggle('open');
                navToggle.classList.toggle('open', open);
                navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            // --- Navbar shadow on scroll (rAF-throttled) ---
            const navbar = document.querySelector('.navbar');
            let ticking = false;
            function onScroll() {
                navbar.classList.toggle('scrolled', document.documentElement.scrollTop > 8);
                ticking = false;
                }
            window.addEventListener('scroll', () => {
                if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
                }, { passive: true });
                onScroll();

            // --- Scroll reveal: cards land like notices pinned to a board ---
            // Stagger is per batch (70ms apart, capped) so far-down cards never wait.
            const revealObserver = new IntersectionObserver((entries) => {
                entries.filter(e => e.isIntersecting).forEach((entry, i) => {
                    entry.target.style.setProperty('--d', Math.min(i * 70, 280) + 'ms');
                    entry.target.classList.add('reveal-visible');
                    revealObserver.unobserve(entry.target);
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

            // --- Count-up on the "Updates Posted" pill ---
            document.querySelectorAll('[data-count]').forEach(el => {
                const target = parseInt(el.dataset.count, 10);
                if (reduceMotion || !target) return;
                el.textContent = '0';
                setTimeout(() => {
                    const start = performance.now(), dur = 700;
                    (function tick(now) {
                        const t = Math.min((now - start) / dur, 1);
                        el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
                        if (t < 1) requestAnimationFrame(tick);
                    })(start);
                }, 330);
            });

            // --- Update details modal ---
            const overlay = document.getElementById("updateModalOverlay");
            const modalBox = document.getElementById("updateModalBox");
            const modalPanel = document.getElementById("updateModalPanel");
            const closeBtn = document.getElementById("updateModalCloseBtn");
            const cards = document.querySelectorAll(".update-card");

            const modalImage = document.getElementById("updateModalImage");
            const modalImageFallback = document.getElementById("updateModalImageFallback");
            const modalCatIcon = document.getElementById("updateModalCatIcon");
            const modalCategory = document.getElementById("updateModalCategory");
            const modalTitle = document.getElementById("updateModalTitle");
            const modalEventDate = document.getElementById("updateModalEventDate");
            const modalEventDateText = document.getElementById("updateModalEventDateText");
            const modalDescription = document.getElementById("updateModalDescription");
            const modalPosted = document.getElementById("updateModalPosted");
            const modalPostedTile = document.getElementById("updateModalPostedTile");
            const doneBtn = document.getElementById("updateModalDoneBtn");

            const CAT_CLASSES = ['cat-alert', 'cat-health', 'cat-project', 'cat-event', 'cat-announcement', 'cat-default'];
            let lastFocused = null;

            function openModal(card) {
                const d = card.dataset;
                lastFocused = card;

                CAT_CLASSES.forEach(c => modalBox.classList.remove(c));
                modalBox.classList.add(d.catClass || 'cat-default');
                modalCatIcon.setAttribute('href', '#i-' + (d.icon || 'default'));

                modalCategory.textContent = d.category || "";
                modalTitle.textContent = d.title || "";
                modalDescription.textContent = d.description || "";
                modalPosted.textContent = d.posted || "";
                modalPostedTile.style.display = d.posted ? "flex" : "none";

                if (d.eventDate && d.eventDate.trim() !== "") {
                    modalEventDateText.textContent = d.eventDate;
                    modalEventDate.style.display = "flex";
                } else {
                    modalEventDate.style.display = "none";
                }

                modalImage.classList.remove('loaded');
                if (d.image && d.image.trim() !== "") {
                    modalImage.onload = () => modalImage.classList.add('loaded');
                    modalImage.src = d.image;
                    if (modalImage.complete) modalImage.classList.add('loaded');
                    modalImage.style.display = "block";
                    modalImageFallback.style.display = "none";
                } else {
                    modalImage.removeAttribute("src");
                    modalImage.style.display = "none";
                    modalImageFallback.style.display = "flex";
                }

                modalPanel.scrollTop = 0;
                modalBox.scrollTop = 0;
                overlay.classList.add("active");
                document.body.style.overflow = "hidden";
                closeBtn.focus({ preventScroll: true });
            }

            function closeModal() {
                overlay.classList.remove("active");
                document.body.style.overflow = "";
                if (lastFocused) lastFocused.focus({ preventScroll: true });
            }

            cards.forEach(card => {
                card.addEventListener("click", () => openModal(card));
                card.addEventListener("keydown", (e) => {
                    if (e.key === "Enter" || e.key === " ") {
                        e.preventDefault();
                        openModal(card);
                    }
                });
            });

            closeBtn.addEventListener("click", closeModal);
            doneBtn.addEventListener("click", closeModal);

            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });

            document.addEventListener("keydown", (e) => {
                if (!overlay.classList.contains("active")) return;
                if (e.key === "Escape") {
                    closeModal();
                } else if (e.key === "Tab") {
                    // keep keyboard focus inside the dialog (close ✕ ↔ Close button)
                    e.preventDefault();
                    (document.activeElement === closeBtn ? doneBtn : closeBtn).focus();
                }
            });
        });
    </script>

</body>
</html>