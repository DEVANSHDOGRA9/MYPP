<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('CSRF token validation failed');
    }

    $user_id = $_SESSION['user_id'];
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

    $query = "SELECT password FROM users_info WHERE id = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($current_password, $hashed_password)) {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $query = "UPDATE users_info SET password = ? WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('si', $new_hashed_password, $user_id);

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
?>
