<?php
session_start();
include_once(__DIR__ . '/../config.php'); // Update this to your actual config file or DB connection setup

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit();
    }

    if ($_POST['action'] === 'delete_user' && isset($_POST['user_id'])) {
        $user_id = (int) $_POST['user_id'];
        $stmt = $mysqli->prepare("DELETE FROM users_info WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete user.']);
        }
        exit();
    }

    if ($_POST['action'] === 'delete_users' && isset($_POST['users'])) {
        $user_ids = json_decode($_POST['users'], true);
        $user_ids = array_map('intval', array_column($user_ids, 'user_id'));
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));

        $stmt = $mysqli->prepare("DELETE FROM users_info WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($user_ids)), ...$user_ids);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Selected users deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete selected users.']);
        }
        exit();
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
