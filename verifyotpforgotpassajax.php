<?php
session_start(); // Start the session

// Include configuration file
include 'config.php'; // This should include the $mysqli variable

// Initialize response array
$response = [];

// Retrieve OTP and CSRF token from POST request
$otp = $_POST['otp'];
$csrf_token = $_POST['csrf_token'];

// Validate CSRF token
if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    $response['error'] = 'Invalid CSRF token';
    echo json_encode($response);
    exit();
}

// Check if OTP is set in session
if (empty($_SESSION['otp']) || empty($_SESSION['otp_email'])) {
    $response['error'] = 'OTP session expired';
    echo json_encode($response);
    exit();
}

// Validate OTP
if ($otp == $_SESSION['otp']) {
    // OTP is valid, proceed to reset password page
    unset($_SESSION['otp']);
    $_SESSION['is_otp_verified']=true;
    $response['success'] = 'OTP verified successfully';

    echo json_encode($response);
} else {
    // OTP is invalid
    $response['error'] = 'Invalid OTP';
    echo json_encode($response);
}
?>
