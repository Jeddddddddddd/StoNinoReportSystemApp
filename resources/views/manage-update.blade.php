<?php
use Illuminate\Support\Facades\DB;

// Strictly enforce admin authorization
if (!session()->has('admin')) {
    redirect('/login')->send();
    exit();
}

$success_msg = "";
$edit_mode = false;
$edit_id = "";
$title = "";
$category = "";
$event_date = "";
$description = "";
$image_path = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim(request('title', ''));
    $category = trim(request('category', ''));
    $event_date = trim(request('event_date', ''));
    $description = trim(request('description', ''));
    $id = request('update_id', '');
    $existing_image = request('existing_image', '');

    $img_path_val = $existing_image;

    // Safe file handling using Laravel's request handler to prevent exceptions
    if (request()->hasFile('image')) {
        $file = request()->file('image');
        if ($file->isValid()) {
            $target_dir = public_path('uploads/');
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $img_name = time() . "_" . $file->getClientOriginalName();
            $file->move($target_dir, $img_name);
            $img_path_val = "uploads/" . $img_name;
        }
    }

    if (!empty($id)) {
        DB::table('barangay_updates')->where('id', $id)->update([
            'title' => $title,
            'category' => $category,
            'event_date' => $event_date,
            'description' => $description,
            'image_path' => $img_path_val,
            'updated_at' => now()
        ]);
        header("Location: " . url('/manage-update'));
        exit();
    } else {
        DB::table('barangay_updates')->insert([
            'title' => $title,
            'category' => $category,
            'event_date' => $event_date,
            'description' => $description,
            'image_path' => $img_path_val,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $success_msg = "New community update posted successfully!";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    DB::table('barangay_updates')->where('id', $id)->delete();
    header("Location: " . url('/manage-update'));
    exit();
}

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $row = DB::table('barangay_updates')->where('id', $edit_id)->first();
    if ($row) {
        $title = $row->title;
        $category = $row->category;
        $event_date = $row->event_date;
        $description = $row->description;
        $image_path = $row->image_path;
    }
}

// Fetch all updates using Laravel Query Builder
$updates_result = DB::table('barangay_updates')->orderBy('created_at', 'desc')->get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Updates - Admin Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/manage-update-style.css') }}">
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
            <h1>Barangay Updates & Events</h1>
            <p>Post announcements or edit existing activities visible on the public updates portal.</p>
        </div>

        <div class="data-section" style="margin-bottom: 40px;">
            <h2><?php echo $edit_mode ? "Edit Update Record" : "Post New Community Update"; ?></h2>
            
            <?php if(!empty($success_msg)): ?>
                <div style="background:#d4edda; color:#155724; padding:12px; border-radius:8px; margin-bottom:20px; font-size:14px;"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <form method="POST" action="{{ url('/manage-update') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="update_id" value="<?php echo $edit_id; ?>">
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($image_path); ?>">

                <div class="admin-form-group">
                    <label>Update Title</label>
                    <input type="text" name="title" required value="<?php echo htmlspecialchars($title); ?>" placeholder="e.g., Barangay Clean-Up Drive">
                </div>

                <div class="admin-form-row-2">
                    <div class="admin-form-group">
                        <label>Category / Venue</label>
                        <input type="text" name="category" required value="<?php echo htmlspecialchars($category); ?>" placeholder="e.g., Announcement / 2ND BATCH GYM">
                    </div>
                    <div class="admin-form-group">
                        <label>Event Date</label>
                        <input type="date" name="event_date" required value="<?php echo htmlspecialchars($event_date); ?>">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label>Upload Banner Image <?php if($edit_mode && !empty($image_path)) echo "(Current image saved)"; ?></label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="admin-form-group">
                    <label>Description Content</label>
                    <textarea name="description" rows="4" required placeholder="Enter full details..."><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <button type="submit" class="btn-submit-admin"><?php echo $edit_mode ? "Save Changes" : "Publish Update"; ?></button>
                <?php if($edit_mode): ?>
                    <a href="{{ url('/manage-update') }}" style="color: #a0a8a4; margin-left: 15px; text-decoration: none; font-size: 14px;">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section-title-wrapper">
            <h2>Existing Published Updates</h2>
        </div>
        
        <div class="updates-list-stream">
            @if ($updates_result->count() > 0)
                @foreach($updates_result as $row)
                    <div class="horizontal-update-card">
                        
                        <div class="horizontal-card-image">
                            @if (!empty($row->image_path))
                                <img src="{{ asset($row->image_path) }}" alt="Update Image">
                            @else
                                <div class="horizontal-image-placeholder">No Image Attached</div>
                            @endif
                        </div>
                        
                        <div class="horizontal-card-body">
                            <div class="horizontal-card-meta">
                                <span class="meta-tag">{{ htmlspecialchars($row->category) }}</span>
                                <span class="meta-date">Event Date: {{ date('M d, Y', strtotime($row->event_date)) }}</span>
                            </div>
                            
                            <h3 class="horizontal-card-title">{{ htmlspecialchars($row->title) }}</h3>
                            <p class="horizontal-card-desc">{!! nl2br(e($row->description)) !!}</p>
                            
                            <div class="horizontal-card-footer">
                                <small class="created-stamp">Posted on: {{ date('M d, Y h:i A', strtotime($row->created_at)) }}</small>
                                <div class="horizontal-card-actions">
                                    <a href="{{ url('/manage-update?edit=' . $row->id) }}" class="action-edit-btn">Edit Details</a>
                                    <a href="{{ url('/manage-update?delete=' . $row->id) }}" class="action-delete-btn" onclick="return confirm('Are you sure you want to delete this update?');">Delete</a>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            @else
                <div class="no-records-box">
                    <p>No community updates found. Post your first update above!</p>
                </div>
            @endif
        </div>
    </div>

</body>
</html>