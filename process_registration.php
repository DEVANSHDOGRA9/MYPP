<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Start session
session_start();

// Database connection details (adjust as per your setup)
include 'config.php';

// Include PHPMailer autoload file
require 'vendor/autoload.php'; // Adjust the path as per your setup

// Function to redirect to OTP verification page
$response = array();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo 'Invalid CSRF token.';
        exit();
    }

    // Initialize error array
    $errors = [];

    // Retrieve form data and sanitize
    $first_name = mysqli_real_escape_string($mysqli, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($mysqli, $_POST['last_name']);
    $email = mysqli_real_escape_string($mysqli, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Simple validation
    if (empty($first_name)) {
        $errors['first_name'] = 'First name is required.';
    }
    if (empty($last_name)) {
        $errors['last_name'] = 'Last name is required.';
    }
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Confirm password is required.';
    }
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // If there are validation errors, output them
    if (!empty($errors)) {
        header('Content-Type: application/json');
        echo json_encode($errors);
        exit();
    }

    // Encrypt the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    $check_query = "SELECT id, is_email_verified FROM users_info WHERE email = '$email'";
    $result = mysqli_query($mysqli, $check_query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $is_email_verified = $row['is_email_verified'];

        if ($is_email_verified == 1) {
            $errors['email'] = 'Email already registered and verified. Please login or enter another email address to register.';
            header('Content-Type: application/json');
            echo json_encode($errors);
            exit();
        } else {
            // Resend OTP if email not verified
            $otp = mt_rand(100000, 999999);
            $_SESSION['temp_otp'] = $otp; // Save OTP in session

            try {
                resendOTP($mysqli, $email, $otp);
                // Respond with JSON for successful OTP resend
                $response["redirect"] = 'verifyotp.php';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } catch (Exception $e) {
                http_response_code(500);
                echo 'Failed to resend verification email. Error: ' . $e->getMessage();
                exit();
            }
        }
    }

    // Insert data into the database
    $sql = "INSERT INTO users_info (first_name, last_name, email, password) VALUES ('$first_name', '$last_name', '$email', '$hashed_password')";

    if (mysqli_query($mysqli, $sql)) {
        // Save last inserted ID in session
        $_SESSION['temp_user_id'] = mysqli_insert_id($mysqli);

        // Generate random 6-digit OTP
        $otp = mt_rand(100000, 999999);

        // Save OTP in session
        $_SESSION['temp_otp'] = $otp;

        try {
            sendOTP($mysqli, $email, $otp);
            // Respond with JSON for successful registration
            $response["redirect"] = 'verifyotp.php';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Failed to send verification email. Error: ' . $e->getMessage();
            exit();
        }

    } else {
        http_response_code(500);
        echo 'Registration failed: ' . mysqli_error($mysqli);
        exit();
    }
} else {
    // If not a POST request, respond with error
    http_response_code(405);
    echo "Method not allowed.";
}

// Close the connection
mysqli_close($mysqli);

// Function to resend OTP
function resendOTP($mysqli, $email, $otp) {
    // PHPMailer initialization
    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'em2.pwh-r1.com'; // SMTP server address
    $mail->SMTPAuth   = true;             // SMTP authentication
    $mail->Username   = 'devanshdogra@orientaloutsourcing.com'; // SMTP username
    $mail->Password   = 'Devansh@2024';   // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;              // SMTP port (typically 587 for STARTTLS)

    // Sender and recipient settings
    $mail->setFrom('devanshdogra@orientaloutsourcing.com', 'Your Name');
    $mail->addAddress($email); // Add a recipient

    // Email content
    $mail->isHTML(true);  // Set email format to HTML
    $mail->Subject = 'Email Verification OTP';
    $mail->Body    = 'Your OTP for email verification is: ' . $otp;

    // Send email
    $mail->send();
    // Note: No need to echo here, as this function is called in try-catch block in main logic.
}

// Function to send OTP after registration
function sendOTP($mysqli, $email, $otp) {
    // PHPMailer initialization
    $mail = new PHPMailer(true);

    // SMTP configuration
    $mail->isSMTP();
    $mail->Host       = 'em2.pwh-r1.com'; // SMTP server address
    $mail->SMTPAuth   = true;             // SMTP authentication
    $mail->Username   = 'devanshdogra@orientaloutsourcing.com'; // SMTP username
    $mail->Password   = 'Devansh@2024';   // SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
    $mail->Port       = 587;              // SMTP port (typically 587 for STARTTLS)

    // Sender and recipient settings
    $mail->setFrom('devanshdogra@orientaloutsourcing.com', 'Your Name');
    $mail->addAddress($email); // Add a recipient

    // Email content
    $mail->isHTML(true);  // Set email format to HTML
    $mail->Subject = 'Email Verification OTP';
    $mail->Body    = 'Your OTP for email verification is: ' . $otp;

    // Send email
    $mail->send();
    // Note: No need to echo here, as this function is called in try-catch block in main logic.
}
?>