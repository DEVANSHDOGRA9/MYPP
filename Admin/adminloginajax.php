<?php
session_start(); // Ensure session is started
include '../config.php'; // Adjust path if necessary

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

header('Content-Type: application/json'); // Ensure JSON response

// Debug: Log received and session CSRF tokens
error_log("Received CSRF Token in adminloginajax.php: " . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'None'));
error_log("Session CSRF Token in adminloginajax.php: " . $_SESSION['csrf_token']);

// Check if CSRF token is valid
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit;
}

// Sanitize and get user inputs
$email = sanitize_input($_POST['email']);
$password = sanitize_input($_POST['password']); // Adjusted to match input field name

// Prepare and execute the SQL query
$query = "SELECT admin_id, password FROM admin_info WHERE email = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    echo json_encode(['status' => 'error', 'message' => 'Database query preparation failed: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

// Check if the email exists
if ($stmt->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
    $stmt->close();
    $mysqli->close();
    exit;
}

// Fetch the admin ID and password hash
$stmt->bind_result($admin_id, $hashed_password);
$stmt->fetch();
$stmt->close();

// Verify the password
if (password_verify($password, $hashed_password)) {
    // Set session variable for admin ID
    $_SESSION['admin_id'] = $admin_id; // Use admin ID from database
    echo json_encode(['status' => 'success', 'message' => 'Login successful!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
}

$mysqli->close();
?>
