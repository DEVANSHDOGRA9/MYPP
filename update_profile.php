<?php
session_start();
// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if user_id is not set
    exit();
}
include 'config.php';
$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Update failed.'];

// Define maximum file size (2 MB in bytes)
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 MB

// Check if form data was posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if CSRF token is valid
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo 'Invalid CSRF token';
        exit;
    }

    // Retrieve and sanitize form data
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $current_profile_image = $_POST['current_profile_image'] ?? '';

    // Validate profile image file type, mime type, and size if a new file is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_file_types = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

        $file_name = basename($_FILES['profile_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_mime_type = mime_content_type($_FILES['profile_image']['tmp_name']);
        $file_size = $_FILES['profile_image']['size'];

        // Check file size
        if ($file_size > MAX_FILE_SIZE) {
            $profile_image = $current_profile_image;
            $response['message'] = 'File size exceeds 2 MB limit.';
        } elseif (in_array($file_ext, $allowed_file_types) && in_array($file_mime_type, $allowed_mime_types)) {
            $upload_dir = 'uploads/';
            $upload_file = $upload_dir . $file_name;

            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_file)) {
                // Delete old profile image if it exists and is not the default image
                if ($current_profile_image && $current_profile_image !== 'default.png') {
                    unlink($upload_dir . $current_profile_image);
                }
                $profile_image = $file_name;
            } else {
                $profile_image = $current_profile_image;
                $response['message'] = 'Failed to upload new profile image.';
            }
        } else {
            $profile_image = $current_profile_image;
            $response['message'] = 'Invalid file type or mime type.';
        }
    } else {
        $profile_image = $current_profile_image;
    }

    // Update user info in database
    $query = "UPDATE users_info SET first_name = ?, last_name = ?, dob = ?, profile_image = ? WHERE id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'ssssi', $first_name, $last_name, $dob, $profile_image, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        // Update user skills
        $skills = $_POST['skills'] ?? [];
        
        // Delete old skills
        $query = "DELETE FROM user_skills WHERE user_id = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        
        // Insert new skills
        foreach ($skills as $skill_id) {
            $query = "INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)";
            $stmt = mysqli_prepare($mysqli, $query);
            mysqli_stmt_bind_param($stmt, 'ii', $user_id, $skill_id);
            mysqli_stmt_execute($stmt);
        }

        $response['success'] = true;
        $response['message'] = 'Profile updated successfully.';
        $response['profile_image'] = $profile_image;
    } else {
        $response['message'] = 'Failed to update user info.';
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($mysqli);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
