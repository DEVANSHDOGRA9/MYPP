<?php
session_start();
include '../config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = ['success' => false, 'message' => 'An error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $response['message'] = 'Invalid CSRF token.';
        echo json_encode($response);
        exit();
    }

    if (!isset($_SESSION['admin_id'])) {
        $response['message'] = 'User not logged in.';
        echo json_encode($response);
        exit();
    }

    $admin_id = $_SESSION['admin_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $profile_image = $_FILES['profile_image'];

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $response['message'] = 'First name, last name, and email are required.';
        echo json_encode($response);
        exit();
    }

    // Check if the email already exists
    $query = "SELECT admin_id FROM admin_info WHERE email = ? AND admin_id != ?";
    $stmt = mysqli_prepare($mysqli, $query);
    if (!$stmt) {
        $response['message'] = 'Prepare failed: ' . mysqli_error($mysqli);
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_bind_param($stmt, 'si', $email, $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $response['message'] = 'The email is already registered.';
        mysqli_stmt_close($stmt);
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_close($stmt);

    $query = "UPDATE admin_info SET first_name = ?, last_name = ?, phone = ?, address = ?, email = ? WHERE admin_id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    if (!$stmt) {
        $response['message'] = 'Prepare failed: ' . mysqli_error($mysqli);
        echo json_encode($response);
        exit();
    }
    mysqli_stmt_bind_param($stmt, 'sssssi', $first_name, $last_name, $phone, $address, $email, $admin_id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($success && $profile_image['size'] > 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($profile_image['type'], $allowed_types)) {
            $response['message'] = 'Invalid file type. Only jpg, png, jpeg, and gif are allowed.';
            echo json_encode($response);
            exit();
        }

        if ($profile_image['size'] > 2 * 1024 * 1024) {
            $response['message'] = 'File size exceeds 2 MB limit.';
            echo json_encode($response);
            exit();
        }

        // Retrieve current profile image path from the database
        $query = "SELECT profile_image FROM admin_info WHERE admin_id = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, 'i', $admin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $current_image);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // Delete the current image if it exists
        if ($current_image && file_exists("uploads/$current_image")) {
            unlink("uploads/$current_image");
        }

        $target_dir = "uploads/";
        $profile_image_name = time() . '-' . basename($profile_image['name']);
        $target_file = $target_dir . $profile_image_name;

        if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
            $profile_image_path = $profile_image_name; // Store only the file name

            $query = "UPDATE admin_info SET profile_image = ? WHERE admin_id = ?";
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, 'si', $profile_image_path, $admin_id);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($success) {
                $response['success'] = true;
                $response['message'] = 'Profile updated successfully.';
                $response['profile_image'] = $profile_image_path;
            } else {
                $response['message'] = 'Failed to update profile image.';
            }
        } else {
            $response['message'] = 'Failed to upload profile image.';
        }
    } elseif ($success) {
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully.';
    } else {
        $response['message'] = 'Failed to update profile.';
    }
}

echo json_encode($response);
?>
