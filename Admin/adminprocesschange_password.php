<?php
session_start();
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    if (!isset($_SESSION['admin_id'])) {
        die('Admin ID not found in session');
    }

    $admin_id = $_SESSION['admin_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo 'Passwords do not match';
        exit;
    }

    $mysqli = new mysqli($servername, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        die('Database connection failed');
    }

    // Fetch the hashed password for the current admin
    $query = "SELECT password FROM admin_info WHERE admin_id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $admin_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $hashed_password)) {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $query = "UPDATE admin_info SET password = ? WHERE admin_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $new_hashed_password, $admin_id);

        if ($stmt->execute()) {
            echo 'success';
        } else {
            echo 'Failed to update password';
        }

        $stmt->close();
    } else {
        echo 'Current password is incorrect';
    }

    $mysqli->close();
} else {
    echo 'Invalid request method';
}
