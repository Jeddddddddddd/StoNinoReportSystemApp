<?php
use Illuminate\Support\Facades\DB;

// Enforce that only logged-in users can access the report portal
if (!session()->has('user') && !session()->has('admin')) {
    redirect('/login')->send();
    exit();
}

$success_msg = "";

// Show success banner after a redirect
if (isset($_GET['submitted']) && $_GET['submitted'] == '1') {$success_msg = "Your report/inquiry has been successfully submitted to the barangay office!";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');$email = trim($_POST['email'] ?? '');$phone = trim($_POST['phone'] ?? '');$location = trim($_POST['location'] ?? '');$issue_type = trim($_POST['issue_type'] ?? '');$user_message = trim($_POST['message'] ?? '');$message = "Issue Type: " . $issue_type . "\nDescription: " . $user_message;
    
    $image_path = "";
    if (request()->hasFile('report_image') && request()->file('report_image')->isValid()) {
        $file = request()->file('report_image');
        $image_name = time() . "_" . $file->getClientOriginalName();
        
        $file->move(public_path('uploads'),$image_name);
        $image_path = "uploads/" . $image_name;
    }

    // DUPLICATE GUARD using Laravel Database Query Builder
    $is_duplicate = DB::table('contact_inquiries')
        ->where('email', $email)
        ->where('message', $message)
        ->where('created_at', '>=', now()->subSeconds(60))
        ->exists();

    if (!$is_duplicate) {
        DB::table('contact_inquiries')->insert([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'message' => $message,
            'image_path' => $image_path,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    header("Location: " . url('/report?submitted=1'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report & Inquiries - Barangay Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/report-style.css') }}">
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
            <a href="{{ url('/updates') }}">Updates</a>
            <a href="{{ url('/about') }}">About</a>
            <a href="{{ url('/report') }}" class="active">Report & Inquiries</a>
            <a href="{{ url('/logout') }}" class="mobile-logout">Log Out</a>
        </nav>

        <a href="{{ url('/logout') }}" class="admin-portal-btn desktop-logout">Log Out</a>
    </header>

    <div class="contact-header">
        <span class="contact-eyebrow">Get in Touch</span>
        <h1>We're here to help</h1>
        <p>Report neighborhood issues like drainage, illegal parking, or send general inquiries.</p>
    </div>

    <!-- Balanced 2-Column Section -->
    <div class="contact-wrapper">
        
        <!-- Left Column: Direct Office Info & Guidelines -->
        <div class="info-box">
            <h3>Reach Out Directly</h3>
            <p>Have an urgent community concern or need assistance? Use the form or check our direct office details below.</p>
            
            <div class="info-item" style="margin-bottom: 20px;">
                <span style="display:block; font-size:12px; font-weight:700; color:#1F3A24; text-transform:uppercase;">Location</span>
                <p>Barangay Sto. Niño Hall, Philippines</p>
            </div>

            <div class="info-item" style="margin-bottom: 20px;">
                <span style="display:block; font-size:12px; font-weight:700; color:#1F3A24; text-transform:uppercase;">Phone / Hotline</span>
                <p>(082) 123-4567 / 0912-345-6789</p>
            </div>

            <div class="info-item" style="margin-bottom: 20px;">
                <span style="display:block; font-size:12px; font-weight:700; color:#1F3A24; text-transform:uppercase;">Email Address</span>
                <p>barangaystonino.portal@gmail.com</p>
            </div>

            <div class="important-notes">
                <h4>Important Guidelines</h4>
                <ul>
                    <li>Please select or type accurate Purok locations for issues like drainage or parking.</li>
                    <li>Attach clear photo proof to help barangay officials act quickly.</li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Send Report Form -->
        <div class="form-box">
            <h3>Send a Report or Message</h3>
            
            @if(!empty($success_msg))
                <div class="alert-success">{{ $success_msg }}</div>
            @endif

            <form id="reportForm" method="POST" action="{{ url('/report') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="e.g., Juan Dela Cruz" value="{{ session('user.full_name', '') }}">
                </div>

                <div class="form-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Email Address</label>
                        <input type="email" id="emailInput" name="email" placeholder="name@gmail.com" value="{{ session('user.email', '') }}">
                        <div id="emailError" class="error-msg">Please enter a valid email address (e.g., user@gmail.com)</div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Mobile Number</label>
                        <input type="text" id="phoneInput" name="phone" placeholder="09123456789" maxlength="11">
                        <div id="phoneError" class="error-msg">Phone must start with 09 and be 11 digits long</div>
                    </div>
                </div>
                <br>

                <div class="form-group">
                    <label>Incident / Issue Location (Purok)</label>
                    <div class="location-input-wrapper">
                        <input type="text" id="locationInput" name="location" required placeholder="Select or type Purok (e.g., Purok 3)">
                        <button type="button" id="arrowToggleBtn" class="dropdown-arrow-btn">&#9662;</button>
                    </div>
                    <div id="purokSuggestions" class="purok-suggestions"></div>
                </div>

                <div class="form-group">
                    <label>Barangay Issue / Problem Type</label>
                    <div class="location-input-wrapper">
                        <input type="text" id="issueInput" name="issue_type" required placeholder="Select or type problem (e.g., Illegal Parking)">
                        <button type="button" id="issueArrowBtn" class="dropdown-arrow-btn">&#9662;</button>
                    </div>
                    <div id="issueSuggestions" class="purok-suggestions"></div>
                </div>

                <div class="form-group">
                    <label>Attach Photo Evidence (Optional)</label>
                    <input type="file" name="report_image" accept="image/*">
                </div>

                <div class="form-group">
                    <label>Your Message / Description</label>
                    <textarea name="message" rows="4" required placeholder="Describe the issue clearly (e.g., clogged drainage causing overflow)..."></textarea>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" required id="consent">
                    <label for="consent" style="display:inline; font-size:12px; text-transform:none; font-weight:normal;">I agree that the information provided will be stored securely in the barangay database solely for processing my report.</label>
                </div>

                <button type="submit" class="btn-submit">Submit Report</button>
            </form>
        </div>

    </div>

    <!-- Separate Full-Width Balanced Container for Recent Reports Status Tracker -->
    <div style="max-width: 1100px; margin: 0 auto 60px auto; padding: 0 20px;">
        <div class="report-status-tracker-box" style="background: #ffffff; border: 1px solid #E2DBC9; border-radius: 20px; padding: 30px; box-shadow: 0 15px 35px rgba(31, 58, 36, 0.06);">
            <h3 style="font-family: 'Georgia', serif; font-size: 24px; margin-bottom: 20px; color: #22201B; border-bottom: 2px solid #E2DBC9; padding-bottom: 10px;">Your Recent Reports Status</h3>
            
            <?php
            if (session()->has('user.email')) {
                $user_email = session('user.email');$user_reports = DB::table('contact_inquiries')
                    ->where('email', $user_email)
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();

                if ($user_reports->count() > 0) {
                    echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">';
                    foreach($user_reports as $t_row) {$status = !empty($t_row->status) ?$t_row->status : 'Pending';
                        
                        $badge_bg = "#fff3cd"; $badge_color = "#856404"; 
                        if ($status == 'In Progress') { $badge_bg = "#cce5ff"; $badge_color = "#004085"; }
                        if ($status == 'Resolved / Accomplished') { $badge_bg = "#d4edda"; $badge_color = "#155724"; }
                        
                        // Clickable card container that pops up the modal details
                        echo '<div class="report-status-card-item" onclick="openReportModal(' . $t_row->id . ')" style="background: #faf8f5; border: 1px solid #E2DBC9; border-radius: 12px; padding: 16px; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">';
                        
                        echo '<div>';
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
                        echo '<span style="padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; background: ' . $badge_bg . '; color: ' . $badge_color . '; text-transform: uppercase;">' . htmlspecialchars($status) . '</span>';
                        echo '<small style="color: #888; font-size: 11px;">' . date('M d, Y', strtotime($t_row->created_at)) . '</small>';
                        echo '</div>';

                        echo '<p style="color: #332; font-size: 13px; line-height: 1.4; margin: 0 0 10px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' . htmlspecialchars($t_row->message) . '</p>';
                        echo '</div>';

                        echo '<span style="color: #1F3A24; font-size: 11px; font-weight: 600;">Click to view full details →</span>';
                        echo '</div>';

                        // Hidden Modal Data Template for each report
                        echo '<div id="modal-data-' . $t_row->id . '" style="display:none;">';
                        echo '<div class="modal-inner-content">';
                        echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">';
                        echo '<span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; background: ' . $badge_bg . '; color: ' . $badge_color . '; text-transform: uppercase;">' . htmlspecialchars($status) . '</span>';
                        echo '<span style="color: #666; font-size: 12px;">Submitted on ' . date('M d, Y h:i A', strtotime($t_row->created_at)) . '</span>';
                        echo '</div>';
                        
                        echo '<p style="font-size: 14px; color: #22201B; line-height: 1.6; margin-bottom: 15px; background: #faf8f5; padding: 12px; border-radius: 8px; border: 1px solid #E2DBC9;"><strong>Description:</strong><br>' . nl2br(e($t_row->message)) . '</p>';

                        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">';
                        
                        // Initial Photo
                        echo '<div>';
                        echo '<strong style="font-size: 12px; color: #555; display: block; margin-bottom: 5px;">Initial Report Photo:</strong>';
                        if (!empty($t_row->image_path)) {
                            echo '<img src="' . asset($t_row->image_path) . '" alt="Initial Photo" style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; cursor: pointer;" onclick="window.open(this.src)">';
                        } else {
                            echo '<p style="font-size: 12px; color: #888; font-style: italic; background: #f0f0f0; padding: 20px; text-align: center; border-radius: 8px;">No initial image attached.</p>';
                        }
                        echo '</div>';

                        // Resolution Photo
                        echo '<div>';
                        echo '<strong style="font-size: 12px; color: #155724; display: block; margin-bottom: 5px;">Fixed / Resolved Proof:</strong>';
                        if (!empty($t_row->resolved_image_path)) {
                            echo '<img src="' . asset($t_row->resolved_image_path) . '" alt="Resolved Photo" style="width: 100%; height: 140px; object-fit: cover; border-radius: 8px; border: 1px solid #c3e6cb; cursor: pointer;" onclick="window.open(this.src)">';
                        } else {
                            echo '<p style="font-size: 12px; color: #888; font-style: italic; background: #f0f0f0; padding: 20px; text-align: center; border-radius: 8px;">Not yet resolved or no proof photo uploaded.</p>';
                        }
                        echo '</div>';

                        echo '</div>'; 
                        echo '</div>'; 
                        echo '</div>'; 
                    }
                    echo '</div>';
                } else {
                    echo '<p style="font-size: 13px; color: #5C574C;">No reports submitted yet.</p>';
                }
            } else {
                echo '<p style="font-size: 13px; color: #5C574C;">Please log in to view report statuses.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Report Details Popup Modal Overlay -->
    <div id="reportModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; padding: 20px;">
        <div style="background: #ffffff; width: 100%; max-width: 600px; border-radius: 16px; padding: 25px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative; animation: modalPop 0.25s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #E2DBC9; padding-bottom: 12px; margin-bottom: 15px;">
                <h3 style="font-family: 'Georgia', serif; font-size: 20px; color: #22201B; margin: 0;">Report Details & Status</h3>
                <button type="button" onclick="closeReportModal()" style="background: none; border: none; font-size: 22px; cursor: pointer; color: #555; font-weight: bold;">&times;</button>
            </div>
            <div id="modalBodyContent">
                <!-- Dynamic content injected here -->
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <button type="button" onclick="closeReportModal()" style="background: #1F3A24; color: #fff; border: none; padding: 8px 20px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>

    <style>
    .report-status-card-item:hover {
        border-color: #1F3A24 !important;
        transform: translateY(-2px);
    }
    @keyframes modalPop {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Mobile nav toggle ---
            const navToggle = document.getElementById('navToggle');
            const navLinks = document.getElementById('navLinks');
            navToggle.addEventListener('click', () => {
                navLinks.classList.toggle('open');
                navToggle.classList.toggle('open');
            });

            // --- 1. Purok Dropdown Logic ---
            const arrowBtn = document.getElementById("arrowToggleBtn");
            const suggestionsBox = document.getElementById("purokSuggestions");
            const locationInput = document.getElementById("locationInput");
            
            for (let i = 1; i <= 23; i++) {
                const div = document.createElement("div");
                div.textContent = `Purok ${i}`;
                div.addEventListener("click", function() {
                    locationInput.value = `Purok ${i}`;
                    suggestionsBox.style.display = "none";
                    arrowBtn.classList.remove("rotate");
                });
                suggestionsBox.appendChild(div);
            }

            arrowBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                issueSuggestions.style.display = "none"; 
                issueArrowBtn.classList.remove("rotate");
                
                if (suggestionsBox.style.display === "block") {
                    suggestionsBox.style.display = "none";
                    arrowBtn.classList.remove("rotate");
                } else {
                    suggestionsBox.style.display = "block";
                    arrowBtn.classList.add("rotate");
                }
            });

            // --- 2. Barangay Issue Dropdown Logic ---
            const issueArrowBtn = document.getElementById("issueArrowBtn");
            const issueSuggestions = document.getElementById("issueSuggestions");
            const issueInput = document.getElementById("issueInput");

            const barangayProblems = [
                "Illegal Parking",
                "Drainage Clogs / Flooding",
                "Broken Streetlights",
                "Uncollected Garbage",
                "Stray Animals",
                "Public Disturbance / Noise Complaint",
                "Road Damage / Potholes",
                "Water Supply Issue",
                "General Inquiry",
                "Other"
            ];

            barangayProblems.forEach(problem => {
                const div = document.createElement("div");
                div.textContent = problem;
                div.addEventListener("click", function() {
                    issueInput.value = problem;
                    issueSuggestions.style.display = "none";
                    issueArrowBtn.classList.remove("rotate");
                });
                issueSuggestions.appendChild(div);
            });

            issueArrowBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                suggestionsBox.style.display = "none"; 
                arrowBtn.classList.remove("rotate");

                if (issueSuggestions.style.display === "block") {
                    issueSuggestions.style.display = "none";
                    issueArrowBtn.classList.remove("rotate");
                } else {
                    issueSuggestions.style.display = "block";
                    issueArrowBtn.classList.add("rotate");
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener("click", function(e) {
                if (!e.target.closest('.location-input-wrapper') && !e.target.closest('.purok-suggestions')) {
                    suggestionsBox.style.display = "none";
                    arrowBtn.classList.remove("rotate");
                    issueSuggestions.style.display = "none";
                    issueArrowBtn.classList.remove("rotate");
                }
            });

            // --- 3. Form Validation ---
            const form = document.getElementById("reportForm");
            const emailInput = document.getElementById("emailInput");
            const phoneInput = document.getElementById("phoneInput");
            const emailError = document.getElementById("emailError");
            const phoneError = document.getElementById("phoneError");

            form.addEventListener("submit", function(e) {
                let isValid = true;
                const emailVal = emailInput.value.trim();
                if (emailVal !== "") {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(emailVal)) {
                        emailError.style.display = "block";
                        emailInput.style.borderColor = "#d9534f";
                        isValid = false;
                    } else {
                        emailError.style.display = "none";
                        emailInput.style.borderColor = "#D5CCD0";
                    }
                }

                const phoneVal = phoneInput.value.trim();
                if (phoneVal !== "") {
                    const phoneRegex = /^09\d{9}$/;
                    if (!phoneRegex.test(phoneVal)) {
                        phoneError.style.display = "block";
                        phoneInput.style.borderColor = "#d9534f";
                        isValid = false;
                    } else {
                        phoneError.style.display = "none";
                        phoneInput.style.borderColor = "#D5CCD0";
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                } else {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = "Submitting...";
                    }
                }
            });
        });

        // Modal Functions
        function openReportModal(reportId) {
            const contentTemplate = document.getElementById('modal-data-' + reportId).innerHTML;
            document.getElementById('modalBodyContent').innerHTML = contentTemplate;
            document.getElementById('reportModalOverlay').style.display = 'flex';
        }

        function closeReportModal() {
            document.getElementById('reportModalOverlay').style.display = 'none';
        }

        window.addEventListener('click', function(e) {
            const overlay = document.getElementById('reportModalOverlay');
            if (e.target === overlay) {
                closeReportModal();
            }
        });
    </script>
</body>
</html>