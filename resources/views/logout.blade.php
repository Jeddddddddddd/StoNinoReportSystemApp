<?php
use Illuminate\Support\Facades\Session;

// Clear out user/admin sessions safely using Laravel session facades
Session::forget(['user', 'admin']);
Session::flush();

// Redirect safely to the Laravel login route
header("Location: " . url('/login'));
exit();
?>