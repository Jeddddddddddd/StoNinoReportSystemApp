<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$error = "";
$full_name = "";
$email = "";
$phone = "";
$security_question = "";

// If already logged in, redirect home
if (session()->has('user')) {
    header("Location: " . url('/'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $security_question = trim($_POST['security_question'] ?? '');
    $security_answer = trim($_POST['security_answer'] ?? '');

    // Validation checks
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address format.";
    } elseif (!preg_match('/^[0-9]{11}$/', $phone)) {
        $error = "Mobile number must be exactly 11 numeric digits.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (empty($security_question) || empty($security_answer)) {
        $error = "Please provide a security question and answer.";
    } else {
        // Check if email already exists
        $existing_user = DB::table('users')->where('email', $email)->first();

        if ($existing_user) {
            $error = "An account with that email already exists.";
        } else {
            $hashed_password = Hash::make($password);
            $hashed_answer = Hash::make(strtolower($security_answer));

            $new_user_id = DB::table('users')->insertGetId([
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed_password,
                'security_question' => $security_question,
                'security_answer' => $hashed_answer,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($new_user_id) {
                session([
                    'user' => [
                        'id' => $new_user_id,
                        'full_name' => $full_name,
                        'email' => $email
                    ]
                ]);
                header("Location: " . url('/'));
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Barangay Portal</title>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('css/auth-style.css') }}">
</head>
<body>

    <div class="auth-card">
        <a href="{{ url('/login') }}" class="auth-brand">
            <img src="{{ asset('assets/legitlogo.png') }}" alt="Barangay Logo" class="brand-logo">
            Barangay<span>Sto.Niño</span>
        </a>

        <div class="portal-tag">Resident Portal</div>
        <h1>Create your account</h1>
        <p class="auth-subtitle">Register to view updates and submit reports</p>
        <div class="divider"></div>

        @if (!empty($error))
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        @endif

        <form action="{{ url('/register') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($full_name); ?>" placeholder="e.g., Juan Dela Cruz">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" placeholder="name@gmail.com">
            </div>

            <div class="form-group">
                <label for="phone">Mobile Number</label>
                <input type="text" id="phone" name="phone" required maxlength="11" value="<?php echo htmlspecialchars($phone); ?>" placeholder="09123456789">
            </div>

            <div class="form-group">
                <label for="security_question">Security Question (for password recovery)</label>
                <select id="security_question" name="security_question" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-top: 5px;">
                    <option value="">Select a security question</option>
                    <option value="What is your mother's maiden name?" @if($security_question == "What is your mother's maiden name?") selected @endif>What is your mother's maiden name?</option>
                    <option value="What was the name of your first pet?" @if($security_question == "What was the name of your first pet?") selected @endif>What was the name of your first pet?</option>
                    <option value="What is your favorite childhood city?" @if($security_question == "What is your favorite childhood city?") selected @endif>What is your favorite childhood city?</option>
                    <option value="What elementary school did you attend?" @if($security_question == "What elementary school did you attend?") selected @endif>What elementary school did you attend?</option>
                </select>
            </div>

            <div class="form-group">
                <label for="security_answer">Security Answer</label>
                <input type="text" id="security_answer" name="security_answer" required placeholder="Your answer (e.g., Santos)">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
            </div>

            <button type="submit" class="auth-btn">Create Account</button>
        </form>

        <div class="auth-switch">
            Already have an account? <a href="{{ url('/login') }}">Log in</a>
        </div>
    </div>

</body>
</html>