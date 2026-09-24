<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$error = "";

// If already logged in, skip straight to the right place using Laravel URL helpers
if (session()->has('user')) {
    header("Location: " . url('/'));
    exit();
}
if (session()->has('admin')) {
    header("Location: " . url('/admin/dashboard'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check admin credentials first
    if ($identifier === 'admin') {
        if ($password === 'stonino') {
            session([
                'admin' => [
                    'id' => 1,
                    'username' => 'admin',
                    'name' => 'Barangay Official'
                ]
            ]);
            header("Location: " . url('/admin/dashboard'));
            exit();
        } else {
            $error = "Invalid email/username or password.";
        }
    } else {
        // Otherwise, check database for resident user via Query Builder
        $user = DB::table('users')->where('email', $identifier)->first();

        if ($user && Hash::check($password, $user->password)) {
            session([
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email
                ]
            ]);
            header("Location: " . url('/'));
            exit();
        } else {
            $error = "Invalid email/username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Login - Barangay Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
</head>
<body>

    <div class="auth-card">
        <a href="{{ url('/login') }}" class="auth-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Logo" class="brand-logo">
            Barangay<span>Sto.Niño</span>
        </a>

        <h1>Welcome back</h1>
        <p class="auth-subtitle">Log in to access community updates and reports</p>
        <div class="divider"></div>

        @if (session('error'))
            <div class="error-msg">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf 
            <div class="form-group">
                <label for="email">Email or Username</label>
                <input type="text" id="email" name="email" placeholder="Enter your email or admin username" required autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-row">
                    <a href="{{ url('/forgot-password') }}">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="auth-btn" id="submitBtn">Log In</button>
        </form>

        <div class="auth-switch" id="registerSwitch">
            Don't have an account yet? <a href="{{ url('/register') }}">Create one</a>
        </div>

    </div>

</body>
</html>