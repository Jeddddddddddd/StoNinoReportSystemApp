<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// --- PUBLIC & AUTHENTICATION PAGES ---

// Show Login Form (GET)
Route::get('/login', function () {
    if (session()->has('user') || session()->has('admin')) {
        return redirect(session()->has('admin') ? '/admin/dashboard' : '/');
    }
    return view('login');
});

// Process Login (POST)
Route::post('/login', function () {
    $identifier = trim(request('email', ''));
    $password = request('password', '');

    // Strict validation check for Admin
    if ($identifier === 'admin' && $password === 'stonino') {
        session([
            'admin' => [
                'id' => 1,
                'username' => 'admin',
                'name' => 'Barangay Official'
            ]
        ]);
        return redirect('/admin/dashboard');
    }

    // Database resident check
    $user = DB::table('users')->where('email', $identifier)->first();

    if ($user && Hash::check($password, $user->password)) {
        session([
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email
            ]
        ]);
        return redirect('/');
    }

    return back()->with('error', 'Invalid email/username or password.');
});

// Registration Routes
Route::get('/register', function () {
    if (session()->has('user') || session()->has('admin')) {
        return redirect('/');
    }
    return view('register');
});
Route::post('/register', function () {
    return view('register');
});

// Forgot Password Routes
Route::get('/forgot-password', function () {
    return view('forgot-password');
});
Route::post('/forgot-password', function () {
    return view('forgot-password');
});

// Reset Password Routes (GET & POST)
Route::get('/reset-password', function () {
    return view('reset-password');
});
Route::post('/reset-password', function () {
    return view('reset-password');
});

// Logout Route
Route::get('/logout', function () {
    Session::forget(['user', 'admin']);
    Session::flush();
    return redirect('/login');
});


// --- SECURE RESIDENT PAGES (Requires Resident or Admin Session) ---

Route::get('/', function () {
    if (!session()->has('user') && !session()->has('admin')) {
        return redirect('/login');
    }
    return view('index');
});

Route::get('/about', function () {
    if (!session()->has('user') && !session()->has('admin')) {
        return redirect('/login');
    }
    return view('about');
});

Route::get('/updates', function () {
    if (!session()->has('user') && !session()->has('admin')) {
        return redirect('/login');
    }
    return view('updates');
});

Route::get('/report', function () {
    if (!session()->has('user') && !session()->has('admin')) {
        return redirect('/login');
    }
    return view('report');
});

Route::post('/report', function () {
    if (!session()->has('user') && !session()->has('admin')) {
        return redirect('/login');
    }
    return view('report');
});


// --- SECURE ADMIN PAGES (Requires Admin Session) ---

Route::get('/admin/dashboard', function () {
    if (!session()->has('admin')) {
        return redirect('/login');
    }
    return view('admin-dashboard');
});

Route::get('/admin/reports', function () {
    if (!session()->has('admin')) {
        return redirect('/login');
    }
    return view('manage-report');
});

Route::post('/admin/reports', function () {
    if (!session()->has('admin')) {
        return redirect('/login');
    }
    return view('manage-report');
});

// Show Manage Updates Page (GET)
Route::get('/manage-update', function () {
    if (!session()->has('admin')) {
        return redirect('/login');
    }
    return view('manage-update');
});

// Process Update / Announcement Creation (POST)
Route::post('/manage-update', function () {
    if (!session()->has('admin')) {
        return redirect('/login');
    }
    return view('manage-update');
});