<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$error = "";
$success = false;
$valid = false;
$user_id = null;

// Look for the token in both GET and POST requests safely using Laravel request helper
$token = request('token', '');

if (!empty($token)) {
    $hashed_token = hash('sha256', $token);
    
    // Convert to Laravel Query Builder for safety and compatibility
    $user = DB::table('users')
        ->where('reset_token', $hashed_token)
        ->first();

    if ($user) {
        // Compare expiry timestamp using PHP's clock
        if (strtotime($user->reset_expires) > time()) {
            $valid = true;
            $user_id = $user->id;
        }
    }
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = Hash::make($password);
        
        DB::table('users')->where('id', $user_id)->update([
            'password' => $hashed_password,
            'reset_token' => null,
            'reset_expires' => null,
            'updated_at' => now()
        ]);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Barangay Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
</head>
<body>

    <div class="auth-card">
        <a href="{{ url('/login') }}" class="auth-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Logo" class="brand-logo">
            Barangay<span>Sto.Niño</span>
        </a>

        <?php if (!$valid): ?>
            <h1>Link invalid or expired</h1>
            <p class="auth-subtitle">Please request a new reset link.</p>
            <div class="divider"></div>
            <div class="auth-switch">
                <a href="{{ url('/forgot-password') }}">&larr; Request a new link</a>
            </div>

        <?php elseif ($success): ?>
            <h1>Password updated</h1>
            <div class="divider"></div>
            <div class="success-msg">Your password has been changed successfully. You can safely log in now.</div>
            <div class="auth-switch">
                <a href="{{ url('/login') }}" class="btn-success-login" style="display: block; text-align: center; text-decoration: none; line-height: 40px;">Go to login portal</a>
            </div>

        <?php else: ?>
            <h1>Set a new password</h1>
            <p class="auth-subtitle">Choose a new password for your account</p>
            <div class="divider"></div>

            <?php if (!empty($error)): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="{{ url('/reset-password') }}?token=<?php echo urlencode($token); ?>" method="POST">
                @csrf
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Repeat your new password">
                </div>
                <button type="submit" class="auth-btn">Update Password</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>