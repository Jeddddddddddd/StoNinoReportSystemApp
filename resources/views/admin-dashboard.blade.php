<?php
// Strictly enforce admin authorization
if (!session()->has('admin')) {
    redirect('/login')->send();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Barangay Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard-style.css') }}">
</head>
<body>

    <header class="navbar">
        <a href="{{ url('/admin/dashboard') }}" class="nav-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Logo" class="brand-logo">
            Admin<span>Portal</span>
        </a>
        <a href="{{ url('/logout') }}" class="logout-btn">Log Out</a>
    </header>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome, Admin</h1>
            <p>Manage community updates and track resident reports in real time.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dash-card">
                <h3>Resident Reports & Inquiries</h3>
                <p>View concerns and reports submitted by residents via the public report page.</p>
                <a href="{{ url('/admin/reports') }}" class="dash-btn">View Reports</a>
            </div>

            <div class="dash-card">
                <h3>Barangay Updates & Events</h3>
                <p>Create, edit, or remove announcements displayed on the public updates portal.</p>
                <a href="{{ url('/manage-update') }}" class="dash-btn">Manage Updates</a>
            </div>
        </div>
    </div>

</body>
</html>