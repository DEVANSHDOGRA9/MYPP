<?php
session_start(); // Start the session

// Include configuration file
include 'config.php'; // This should include the $mysqli variable

$response = [];

// Check if CSRF token is valid
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $response['error'] = "Invalid CSRF token";
    echo json_encode($response);
    exit();
}

// Check if new password and confirm password are set
if (!isset($_POST['new_password'], $_POST['confirm_password'])) {
    $response['error'] = "Password fields are required";
    echo json_encode($response);
    exit();
}

$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

// Check if passwords match
if ($new_password !== $confirm_password) {
    $response['error'] = "Passwords do not match";
    echo json_encode($response);
    exit();
}

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

// Get the email from the session
$email = $_SESSION['otp_email'];

if (empty($email)) {
    $response['error'] = "Invalid request";
    echo json_encode($response);
    exit();
}

try {
    // Update the password in the database
    $stmt = $mysqli->prepare("UPDATE users_info SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Clear the OTP from the session
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);

        $response['success'] = "Password has been reset successfully";
    } else {
        $response['error'] = "Failed to reset the password. Please try again.";
    }

    $stmt->close();
} catch (Exception $e) {
    $response['error'] = "Error: " . $e->getMessage();
}

echo json_encode($response);
?>
