<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$error = "";
$success = "";

// If this is a fresh GET request (not a form submission), 
// you can optionally reset or keep steps. Let's make sure step 1 starts clean if no email is set.
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!session()->has('reset_step') || !session()->has('reset_email')) {
        session(['reset_step' => 1]);
    }
}

$step = session('reset_step', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Step 1: User submits email
    if ($action === 'check_email') {
        $email = trim($_POST['email'] ?? '');
        $user = DB::table('users')->where('email', $email)->first();

        if ($user) {
            session([
                'reset_email' => $email,
                'security_question' => $user->security_question,
                'reset_step' => 2
            ]);
            $step = 2;
        } else {
            $error = "No account found with that email address.";
        }
    } 
    // Step 2: User answers security question
    elseif ($action === 'verify_answer') {
        $answer = trim($_POST['answer'] ?? '');
        $email = session('reset_email', '');

        $user = DB::table('users')->where('email', $email)->first();

        if ($user) {
            // Verify lowercase answer against stored hash
            if (Hash::check(strtolower($answer), $user->security_answer)) {
                session(['reset_step' => 3]);
                $step = 3;
            } else {
                $error = "Incorrect security answer. Please try again.";
                $step = 2;
            }
        } else {
            $error = "Session expired. Please start over.";
            session()->forget(['reset_step', 'reset_email', 'security_question']);
            $step = 1;
        }
    } 
    // Step 3: User sets new password
    elseif ($action === 'update_password') {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $email = session('reset_email', '');

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
            $step = 3;
        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";
            $step = 3;
        } else {
            $hashed_password = Hash::make($password);
            
            DB::table('users')->where('email', $email)->update([
                'password' => $hashed_password,
                'updated_at' => now()
            ]);

            $success = "Password updated successfully! You can now log in.";
            session()->forget(['reset_step', 'reset_email', 'security_question']); // Clear reset session data
        }
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

        <h1>Reset Password</h1>
        <p class="auth-subtitle">
            @if ($step === 1) Enter your email to look up your account
            @elseif ($step === 2) Answer your security question
            @else Choose a new password @endif
        </p>
        <div class="divider"></div>

        @if (!empty($error))
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        @endif

        @if (!empty($success))
            <div class="success-msg" style="padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <div class="auth-switch">
                <a href="{{ url('/login') }}" class="auth-btn" style="display: block; text-align: center; text-decoration: none; line-height: 40px;">Go to Login</a>
            </div>
        @else

            @if ($step === 1)
                <form action="{{ url('/forgot-password') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="check_email">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required autocomplete="off" placeholder="name@gmail.com">
                    </div>
                    <button type="submit" class="auth-btn">Next</button>
                </form>

            @elseif ($step === 2)
                <form action="{{ url('/forgot-password') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="verify_answer">
                    <div class="form-group">
                        <label style="font-weight: bold; color: #333; margin-bottom: 8px; display: block;">Security Question:</label>
                        <div style="background: #f8f9fa; padding: 12px; border-radius: 6px; border: 1px solid #ddd; margin-bottom: 15px; color: #333; font-weight: 500;">
                    <?php echo htmlspecialchars(session('security_question', '')); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="answer">Your Answer</label>
                        <input type="text" id="answer" name="answer" required autocomplete="off" placeholder="Enter your secret answer">
                    </div>
                    <button type="submit" class="auth-btn">Verify Answer</button>
                </form>

            @elseif ($step === 3)
            <form action="{{ url('/forgot-password') }}" method="POST">
                @csrf
                    <input type="hidden" name="action" value="update_password">
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
            @endif

        @endif

        <div class="auth-switch" style="margin-top: 20px;">
            <a href="{{ url('/login') }}">&larr; Back to login</a>
        </div>
    </div>

</body>
</html>