<?php
include_once(__DIR__ . '/../config.php');

header('Content-Type: application/json');
$response = [];
// Default HTTP status code

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $response['error'] = 'Invalid CSRF token.';

        echo json_encode($response);
    } else {
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';

        if ($action == 'update_profile') {
            $firstName = isset($_POST['first_name']) ? $mysql->real_escape_string(trim($_POST['first_name'])) : '';
            $lastName = isset($_POST['last_name']) ? $mysql->real_escape_string(trim($_POST['last_name'])) : '';
            $email = isset($_POST['email']) ? $mysql->real_escape_string(trim($_POST['email'])) : '';
            $phone = isset($_POST['phone']) ? $mysql->real_escape_string(trim($_POST['phone'])) : '';
            $address = isset($_POST['address']) ? $mysql->real_escape_string(trim($_POST['address'])) : '';
            $profileImage = isset($_FILES['profile_image']['name']) && !empty($_FILES['profile_image']['name']) ? $_FILES['profile_image'] : null;

            // Validate input
            if (empty($firstName)) {
                $response['error'][] = 'First Name is required.';
            }
            if (empty($lastName)) {
                $response['error'][] = 'Last Name is required.';
            }
            if (empty($email)) {
                $response['error'][] = 'Email is required.';
            } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['error'][] = 'Invalid email format.';
            }
            else {
                // Check if the new email is already used by another admin
                $userId = $_SESSION['admin_id'];
                $emailCheckQry = "SELECT COUNT(*) as count FROM `admin_info` WHERE `email` = '$email' AND `id` != $userId";
                $emailCheckResult = $mysql->query($emailCheckQry);
                $emailCheck = $emailCheckResult->fetch_assoc();
                if ($emailCheck['count'] > 0) {
                    $response['error'][] = 'Email address is already in use by another admin.';
                }
            }
            if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
                $response['error'][] = 'Invalid phone number format.';
            }
            if ($profileImage != null) {
                // var_dump($profileImage);die;
                $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($profileImage['type'], $validImageTypes)) {
                    $response['error'][] = 'Invalid image type. Only JPG, PNG, and GIF are allowed.';
                }
                if ($profileImage['size'] > 2 * 1024 * 1024) { // 2MB
                    $response['error'][] = 'Image size exceeds 2MB.';
                }
            }

            if (empty($response['error'])) {
                // No validation error, proceed to update the profile
                $userId = $_SESSION['admin_id'];

                // Get current profile image path
                $currentImageQry = "SELECT profile_image FROM `admin_info` WHERE id = $userId";
                $result = $mysql->query($currentImageQry);
                $user = $result->fetch_assoc();
                $currentImagePath = $user['profile_image'];

                // Update user information
                $updateQry = "UPDATE `admin_info` SET `first_name` = '$firstName', `last_name` = '$lastName', `email` = '$email', `phone` = '$phone', `address` = '$address' WHERE `id` = $userId";

                if ($mysql->query($updateQry)) {
                    if ($profileImage) {
                        // Delete old image file if it exists
                        if (file_exists($currentImagePath) && $currentImagePath != 'uploads/default.jpg') {
                            unlink($currentImagePath);
                        }

                        // Upload new image
                        $targetDir = "uploads/";
                        $targetFile = $targetDir . basename($profileImage["name"]);
                        move_uploaded_file($profileImage["tmp_name"], $targetFile);

                        // Update database with new image path
                        $imageUpdateQry = "UPDATE `admin_info` SET profile_image = '$targetFile' WHERE id = $userId";
                        $mysql->query($imageUpdateQry);
                    }

                    $response['success'] = true;
                    $response['message'] = 'Profile updated successfully!';
                } else {
                    $response['error'] = 'Failed to update profile. Please try again later.';
                }
            } else {
                //
                // echo json_encode($response);
            }

            echo json_encode($response);
        }

        if ($action == 'update_password') {
            $currentPassword = isset($_POST['current_password']) ? $mysql->real_escape_string(trim($_POST['current_password'])) : '';
            $newPassword = isset($_POST['new_password']) ? $mysql->real_escape_string(trim($_POST['new_password'])) : '';
            $confirmNewPassword = isset($_POST['confirm_new_password']) ? $mysql->real_escape_string(trim($_POST['confirm_new_password'])) : '';

            // Check if any password field is filled
            $passwordFieldsFilled = !empty($currentPassword) || !empty($newPassword) || !empty($confirmNewPassword);

            if ($passwordFieldsFilled) {
                // If any password field is filled, all three must be filled
                if (empty($currentPassword)) {
                    $response['error'][] = 'Current Password is required.';
                }
                if (empty($newPassword)) {
                    $response['error'][] = 'New Password is required.';
                }
                if (empty($confirmNewPassword)) {
                    $response['error'][] = 'Confirm New Password is required.';
                } else if ($newPassword !== $confirmNewPassword) {
                    $response['error'][] = 'Passwords do not match.';
                }
            }

            if (empty($response['error'])) {
                $userId = $_SESSION['admin_id'];
                $qry = "SELECT `password` FROM `admin_info` WHERE `id` = $userId";
                $result = $mysql->query($qry);
                $user = $result->fetch_assoc();

                if ($passwordFieldsFilled && !password_verify($currentPassword, $user['password'])) {
                    $response['error'][] = 'Incorrect current password.';
                } else if ($passwordFieldsFilled) {
                    $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $updateQry = "UPDATE `admin_info` SET `password` = '$hashedNewPassword' WHERE `id` = $userId";

                    if ($mysql->query($updateQry)) {
                        $response['success'] = true;
                        $response['message'] = 'Password updated successfully!';
                    } else {
                        $response['error'] = 'Failed to update password. Please try again later.';
                    }
                }
            } else {

                // echo json_encode($response);
            }

            echo json_encode($response);
        }
    }
} else {
    $response['error'] = 'Invalid request method.';

    echo json_encode($response);
}
