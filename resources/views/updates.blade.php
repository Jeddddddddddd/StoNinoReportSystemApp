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
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/update-style.css') }}">
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
            <a href="{{ url('/') }}">Home</a>
            <a href="{{ url('/updates') }}" class="active">Updates</a>
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/report') }}">Report & Inquiries</a>
            <a href="{{ url('/logout') }}" class="mobile-logout">Log Out</a>
        </nav>

        <a href="{{ url('/logout') }}" class="admin-portal-btn desktop-logout">Log Out</a>
    </header>

    <div class="updates-container">
        <div class="updates-header reveal">
            <span class="updates-eyebrow">Barangay Portal</span>
            <h1>Community Updates & Events</h1>
            <p>Stay informed on upcoming projects, community repairs, programs, and announcements from Barangay Sto. Niño.</p>

            @if ($updates->count() > 0)
                <div class="updates-meta-bar">
                    <span class="updates-count-pill">{{ $updates->count() }} {{ $updates->count() === 1 ? 'Update' : 'Updates' }} Posted</span>
                    <span class="updates-hint">Click any update to view full details</span>
                </div>
            @endif
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
                    ?>
                    <div class="update-card reveal {{ $catClass }}"
                         tabindex="0"
                         role="button"
                         aria-haspopup="dialog"
                         data-title="{{ htmlspecialchars($row->title) }}"
                         data-category="{{ htmlspecialchars($row->category) }}"
                         data-cat-class="{{ $catClass }}"
                         data-description="{{ htmlspecialchars($row->description) }}"
                         data-image="{{ $has_image ? asset($row->image_path) : '' }}"
                         data-event-date="{{ htmlspecialchars($event_date_raw) }}"
                         data-posted="{{ htmlspecialchars($posted_raw) }}">

                        <span class="update-index">{{ $indexNum }}</span>

                        <div class="update-img-wrapper">
                            @if ($has_image)
                                <img src="{{ asset($row->image_path) }}" alt="Update Image" class="update-img" loading="lazy">
                            @else
                                <div class="update-placeholder">
                                    <span class="placeholder-icon">🏘️</span>
                                    <span>No image provided</span>
                                </div>
                            @endif
                            @if (!empty($event_date_raw))
                                <div class="img-date-overlay">📅 {{ $event_date_raw }}</div>
                            @endif
                        </div>

                        <div class="update-content">
                            <div class="update-content-top">
                                <span class="update-category">{{ htmlspecialchars($row->category) }}</span>
                                <h3>{{ htmlspecialchars($row->title) }}</h3>
                                <p>{!! nl2br(e($row->description)) !!}</p>
                            </div>

                            <div class="update-footer">
                                <span class="posted-date"><span class="dot-icon"></span>Posted on {{ $posted_raw }}</span>
                                <span class="view-details-link">View full details <span class="arrow">&rarr;</span></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="no-updates reveal">
                    <span class="no-updates-icon">📭</span>
                    <h3>No updates available right now.</h3>
                    <p>Check back later for upcoming barangay events and announcements.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="modal-overlay" id="updateModalOverlay">
        <div class="modal-box" id="updateModalBox" role="dialog" aria-modal="true" aria-labelledby="updateModalTitle">
            <button type="button" class="modal-close" id="updateModalCloseBtn" aria-label="Close">&times;</button>

            <div class="modal-img-wrapper" id="updateModalImageWrapper">
                <img id="updateModalImage" alt="Update image">
                <div class="modal-img-fallback" id="updateModalImageFallback">
                    <span>🏘️</span>
                </div>
            </div>

            <div class="modal-details-panel">
                <div class="modal-details-scroll">
                    <span class="update-category" id="updateModalCategory"></span>
                    <h3 id="updateModalTitle"></h3>

                    <div class="event-date-badge" id="updateModalEventDate" style="display:none;"></div>

                    <div class="modal-divider"></div>

                    <p id="updateModalDescription" class="modal-full-description"></p>

                    <div class="update-footer" id="updateModalPosted"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Mobile nav toggle ---
            const navToggle = document.getElementById('navToggle');
            const navLinks = document.getElementById('navLinks');
            navToggle.addEventListener('click', () => {
                navLinks.classList.toggle('open');
                navToggle.classList.toggle('open');
            });

            // --- Fade-in scroll reveal ---
            const revealEls = document.querySelectorAll('.reveal');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('reveal-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12 });
            revealEls.forEach(el => revealObserver.observe(el));

            // Stagger the card fade-ins based on their order
            const cardEls = document.querySelectorAll('.update-card.reveal');
            cardEls.forEach((card, i) => {
                card.style.transitionDelay = Math.min(i * 0.09, 0.45) + 's';
            });

            const overlay = document.getElementById("updateModalOverlay");
            const modalBox = document.getElementById("updateModalBox");
            const closeBtn = document.getElementById("updateModalCloseBtn");
            const cards = document.querySelectorAll(".update-card");

            const modalImageWrapper = document.getElementById("updateModalImageWrapper");
            const modalImage = document.getElementById("updateModalImage");
            const modalImageFallback = document.getElementById("updateModalImageFallback");
            const modalCategory = document.getElementById("updateModalCategory");
            const modalTitle = document.getElementById("updateModalTitle");
            const modalEventDate = document.getElementById("updateModalEventDate");
            const modalDescription = document.getElementById("updateModalDescription");
            const modalPosted = document.getElementById("updateModalPosted");

            const CAT_CLASSES = ['cat-alert', 'cat-health', 'cat-project', 'cat-event', 'cat-announcement', 'cat-default'];

            function openModal(card) {
                const d = card.dataset;

                // Reset then apply this update's category color theme to the modal
                CAT_CLASSES.forEach(c => modalBox.classList.remove(c));
                modalBox.classList.add(d.catClass || 'cat-default');

                modalCategory.textContent = d.category || "";
                modalTitle.textContent = d.title || "";
                modalDescription.textContent = d.description || "";
                modalPosted.textContent = d.posted ? ("Posted on " + d.posted) : "";

                if (d.eventDate && d.eventDate.trim() !== "") {
                    modalEventDate.textContent = "📅 Event Date: " + d.eventDate;
                    modalEventDate.style.display = "inline-flex";
                } else {
                    modalEventDate.style.display = "none";
                }

                if (d.image && d.image.trim() !== "") {
                    modalImage.src = d.image;
                    modalImage.style.display = "block";
                    modalImageFallback.style.display = "none";
                } else {
                    modalImage.removeAttribute("src");
                    modalImage.style.display = "none";
                    modalImageFallback.style.display = "flex";
                }

                overlay.classList.add("active");
                document.body.style.overflow = "hidden";
                closeBtn.focus();
            }

            function closeModal() {
                overlay.classList.remove("active");
                document.body.style.overflow = "";
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

            overlay.addEventListener("click", (e) => {
                if (e.target === overlay) {
                    closeModal();
                }
            });

            document.addEventListener("keydown", (e) => {
                if (e.key === "Escape" && overlay.classList.contains("active")) {
                    closeModal();
                }
            });
        });
    </script>

</body>
</html>