<?php
session_start();
include '../config.php';

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
        $profile_image = $_FILES['profile_image'];

        if (empty($first_name) || empty($last_name)) {
            $response['message'] = 'First name and last name are required.';
            echo json_encode($response);
            exit();
        }

        $query = "UPDATE admin_info SET first_name = ?, last_name = ?, phone = ?, address = ? WHERE admin_id = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        if (!$stmt) {
            $response['message'] = 'Prepare failed: ' . mysqli_error($mysqli);
            echo json_encode($response);
            exit();
        }
        mysqli_stmt_bind_param($stmt, 'ssssi', $first_name, $last_name, $phone, $address, $admin_id);
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

            $target_dir = "../uploads/";
            $profile_image_name = time() . '-' . basename($profile_image['name']);
            $target_file = $target_dir . $profile_image_name;

            if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
                $profile_image_path = 'uploads/' . $profile_image_name;

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
    // } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $response['message'] = 'Passwords do not match.';
            echo json_encode($response);
            exit();
        }

        $mysqli = new mysqli($servername, $username, $password, $dbname);

        if ($mysqli->connect_error) {
            $response['message'] = 'Database connection failed.';
            echo json_encode($response);
            exit();
        }

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
                $response['success'] = true;
                $response['message'] = 'Password updated successfully.';
            } else {
                $response['message'] = 'Failed to update password.';
            }

            $stmt->close();
        } else {
            $response['message'] = 'Current password is incorrect.';
        }

        $mysqli->close();
    // } 
    // else {
        // $response['message'] = 'Invalid action.';
    // }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
