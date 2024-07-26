<?php
session_start(); // Ensure session is started

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

// Function to establish database connection
function get_db_connection() {
    include 'config.php';
    if ($mysqli->connect_error) {
        die('Database connection failed');
    }
    return $mysqli;
}

// Debug: Log received and session CSRF tokens
error_log("Received CSRF Token in loginajax.php: " . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'None'));
error_log("Session CSRF Token in loginajax.php: " . $_SESSION['csrf_token']);

// Check if CSRF token is valid
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo 'Invalid CSRF token';
    exit;
}

// Sanitize and get user inputs
$email = sanitize_input($_POST['email']);
$password = sanitize_input($_POST['pwd']);

// Establish database connection
$mysqli = get_db_connection();

// Prepare and execute the SQL query
$query = "SELECT password, is_email_verified FROM users_info WHERE email = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo 'Database query preparation failed';
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

// Check if the email exists
if ($stmt->num_rows === 0) {
    echo 'Invalid email or password';
    $stmt->close();
    $mysqli->close();
    exit;
}

// Fetch the password hash and email verification status
$stmt->bind_result($hashed_password, $is_email_verified);
$stmt->fetch();
$stmt->close();

// Check if the email is verified
if ($is_email_verified != 1) {
    echo 'Email not verified';
    $mysqli->close();
    exit;
}

// Verify the password
if (password_verify($password, $hashed_password)) {
    // Set session variable or any other login logic
    $_SESSION['user_id'] = $email; // Example session variable
    echo 'success'; // Respond with success
} else {
    echo '<span class="error-response">Invalid email or password</span>'; // Respond with error
}

$mysqli->close();
?>
