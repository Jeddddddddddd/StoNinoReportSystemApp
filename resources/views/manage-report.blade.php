<?php
use Illuminate\Support\Facades\DB;

// Strictly enforce admin authorization
if (!session()->has('admin')) {
    redirect('/login')->send();
    exit();
}

$error_msg = "";
$success_msg = "";

if (isset($_GET['updated'])) {
    $success_msg = "Report status successfully updated!";
}
if (isset($_GET['error']) && $_GET['error'] == 'image_required') {
    $error_msg = "Error: A proof of resolution photo is required to mark this report as solved.";
}

// Handle status updates from admin with validation check
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['report_id'], $_POST['new_status'])) {
    $report_id = intval($_POST['report_id']);
    $new_status = trim($_POST['new_status']);

    // Check if changing to Resolved without a photo and no previous photo exists
    if ($new_status === 'Resolved / Accomplished') {
        $current_report = DB::table('contact_inquiries')->where('id', $report_id)->first();
        $has_new_file = request()->hasFile('resolved_image') && request()->file('resolved_image')->isValid();
        $has_existing_file = !empty($current_report->resolved_image_path);

        if (!$has_new_file && !$has_existing_file) {
            header("Location: " . url('/admin/reports?error=image_required'));
            exit();
        }
    }

    $updateData = [
        'status' => $new_status,
        'updated_at' => now()
    ];

    if (request()->hasFile('resolved_image') && request()->file('resolved_image')->isValid()) {
        $file = request()->file('resolved_image');
        $image_name = "resolved_" . time() . "_" . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $image_name);
        $updateData['resolved_image_path'] = "uploads/" . $image_name;
    }

    DB::table('contact_inquiries')
        ->where('id', $report_id)
        ->update($updateData);
    
    header("Location: " . url('/admin/reports?updated=1'));
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    DB::table('contact_inquiries')->where('id', $id)->delete();
    
    header("Location: " . url('/admin/reports'));
    exit();
}

// Fetch all reports using Laravel Query Builder
$reports = DB::table('contact_inquiries')->orderBy('created_at', 'desc')->get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reports - Admin Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/manage-report-style.css') }}">
</head>
<body>

    <header class="navbar">
        <a href="{{ url('/admin/dashboard') }}" class="nav-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Logo" class="brand-logo">
            Admin<span>Portal</span>
        </a>
        <a href="{{ url('/admin/dashboard') }}" class="logout-btn">Back to Dashboard</a>
    </header>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Resident Reports & Inquiries</h1>
            <p>Track who submitted information, review resident locations, and manage incoming concerns.</p>
        </div>

        @if(!empty($success_msg))
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px;">{{ $success_msg }}</div>
        @endif
        @if(!empty($error_msg))
            <div style="background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;">{{ $error_msg }}</div>
        @endif

        <div class="data-section">
            <h2>Submitted Inquiries List</h2>
            <div class="cards-container">
                @if ($reports->count() > 0)
                    @foreach($reports as $row)
                        <?php 
                            $status = !empty($row->status) ? $row->status : 'Pending';
                            $color = '#856404'; $bg = '#fff3cd';
                            if($status == 'In Progress') { $color = '#004085'; $bg = '#cce5ff'; }
                            if($status == 'Resolved / Accomplished') { $color = '#155724'; $bg = '#d4edda'; }
                            
                            $timestamp = strtotime($row->created_at);
                            $formatted_date = date("M d, Y", $timestamp);
                            $formatted_time = date("h:i A", $timestamp);
                        ?>
                        
                        <div class="report-card">
                            <div class="card-image-section">
                                <?php 
                                    $image_file = '';
                                    if (!empty($row->image_path)) { $image_file = $row->image_path; }
                                    elseif (!empty($row->image)) { $image_file = $row->image; }
                                    elseif (!empty($row->photo)) { $image_file = $row->photo; }

                                    if(!empty($image_file)): 
                                ?>
                                    <img src="{{ asset($image_file) }}" alt="Report Attachment" class="report-img" onclick="window.open(this.src)">
                                <?php else: ?>
                                    <div class="no-image-placeholder">
                                        <span>No Image Attached</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-details-section">
                                <div class="card-meta-header">
                                    <span class="report-id">ID: #{{ $row->id }}</span>
                                    <span class="report-date">📅 {{ $formatted_date }} at {{ $formatted_time }}</span>
                                </div>
                                
                                <h3 class="resident-name">{{ htmlspecialchars($row->name) }}</h3>
                                
                                <div class="resident-contact-grid">
                                    <div><strong>Email:</strong> {{ htmlspecialchars($row->email) }}</div>
                                    <div><strong>Phone:</strong> {{ htmlspecialchars($row->phone) }}</div>
                                    <div><strong>Purok/Location:</strong> {{ htmlspecialchars($row->location) }}</div>
                                </div>

                                <div class="message-box">
                                    {!! nl2br(e($row->message)) !!}
                                 </div>
                            </div>

                            <div class="card-action-section">
                                <div class="status-badge-container">
                                    <span class="status-badge" style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                                        {{ htmlspecialchars($status) }}
                                    </span>
                                </div>

                                <form method="POST" action="{{ url('/admin/reports') }}" class="status-form" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="report_id" value="{{ $row->id }}">
                                    <select name="new_status" class="status-select-dropdown" data-id="{{ $row->id }}">
                                        <option value="Pending" @if($status=='Pending') selected @endif>Pending</option>
                                        <option value="In Progress" @if($status=='In Progress') selected @endif>In Progress</option>
                                        <option value="Resolved / Accomplished" @if($status=='Resolved / Accomplished') selected @endif>Resolved / Accomplished</option>
                                    </select>

                                    <!-- Proof of resolution photo upload field (Hidden unless Resolved is selected) -->
                                    <div class="resolved-image-group-{{ $row->id }}" style="display: @if($status=='Resolved / Accomplished') block @else none @endif; margin-top: 6px;">
                                        <label style="font-size: 11px; color: #d4af37; display: block; margin-bottom: 4px;">Proof of Resolution Photo:</label>
                                        <input type="file" name="resolved_image" accept="image/*" style="font-size: 11px; color: #fff; width: 100%;">
                                        @if(!empty($row->resolved_image_path))
                                            <small style="color: #a0a8a4; display: block; margin-top: 2px;">Resolution photo already attached.</small>
                                        @endif
                                    </div>

                                    <button type="submit" class="btn-save" style="margin-top: 8px;">Save Status</button>
                                </form>

                                <a href="{{ url('/admin/reports?delete=' . $row->id) }}" class="btn-delete" onclick="return confirm('Delete this report?');">Delete Report</a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="no-records">No resident reports found.</div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.status-form').forEach(function(form) {
            const selectTag = form.querySelector('select[name="new_status"]');
            const reportId = selectTag.getAttribute('data-id');
            const uploadDiv = form.querySelector('.resolved-image-group-' + reportId);

            selectTag.addEventListener('change', function() {
                if (this.value === 'Resolved / Accomplished') {
                    uploadDiv.style.display = 'block';
                } else {
                    uploadDiv.style.display = 'none';
                }
            });

            form.addEventListener('submit', function() {
                const btn = form.querySelector('.btn-save');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                }
            });
        });
    </script>
</body>
</html>