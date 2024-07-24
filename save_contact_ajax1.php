<?php
// Include Composer autoload file
require 'vendor/autoload.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include configuration file for MySQLi connection
include 'config.php';

// Initialize session (if not already initialized)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables for form data and errors
$nameErr = $emailErr = $messageErr = "";
$name = $email = $message = "";
$formValid = true;

// Function to sanitize and validate input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid CSRF token.'));
    exit;
}

// Validate name
if (empty($_POST["name"])) {
    $nameErr = "Name is required";
    $formValid = false;
} else {
    $name = test_input($_POST["name"]);
}

// Validate email
if (empty($_POST["email"])) {
    $emailErr = "Email is required";
    $formValid = false;
} else {
    $email = test_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
        $formValid = false;
    }
}

// Validate message
if (empty($_POST["message"])) {
    $messageErr = "Message is required";
    $formValid = false;
} else {
    $message = test_input($_POST["message"]);
}

// Validate file upload (optional)
$file_uploaded = false;
$file_path = '';
$file_name = '';
if (!empty($_FILES['file']['name'])) {
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_type = $_FILES['file']['type'];
    $file_error = $_FILES['file']['error'];

    // Check file size (max 5MB)
    $max_file_size = 5 * 1024 * 1024; // 5MB in bytes
    if ($file_size > $max_file_size) {
        echo json_encode(array('status' => 'error', 'message' => 'File size exceeds maximum allowed (5MB).'));
        exit;
    }

    // Allowed file types
    $allowed_extensions = ['docx', 'pdf', 'xlsx'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_extensions)) {
        echo json_encode(array('status' => 'error', 'message' => 'Only DOCX, PDF, XLSX files are allowed.'));
        exit;
    }

    // Move uploaded file to uploads directory
    $upload_dir = __DIR__ . '/uploads/';
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file_tmp, $file_path)) {
        echo json_encode(array('status' => 'error', 'message' => 'Failed to move uploaded file.'));
        exit;
    }

    $file_uploaded = true;
}

// If form is valid, send email and save to database
if ($formValid) {
    // Instantiate PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'em2.pwh-r1.com';  // Specify your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'devanshdogra@orientaloutsourcing.com'; // SMTP username
        $mail->Password = 'Devansh@2024'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port = 587; // TCP port to connect to

        // Recipients
        $mail->setFrom($email, $name); // Sender's email and name
        $mail->addAddress('chetan@orientaloutsourcing.com'); // Recipient's email

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'New Contact Form Submission';
        $mail->Body    = "Name: $name<br>Email: $email<br>Message: $message";

        // Add attachment if file was uploaded
        if ($file_uploaded) {
            $mail->addAttachment($file_path, $file_name);
        }

        // Send email
        if (!$mail->send()) {
            // Display error message
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        } else {
            // Save form data to database
            $stmt = $conn->prepare("INSERT INTO task4 (contact_name, contact_email, contact_message, contact_file) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $message, $file_name);

            if ($stmt->execute()) {
                // Clear form fields
                $name = $email = $message = "";
                // Send success response to AJAX
                echo json_encode(array('status' => 'success', 'message' => 'Your message has been successfully sent. We will get back to you shortly.'));
                exit;
            } else {
                // Handle database insertion error
                echo json_encode(array('status' => 'error', 'message' => 'Error saving data to database.'));
                exit;
            }

            $stmt->close();
        }
    } catch (Exception $e) {
        // Display error message
        echo json_encode(array('status' => 'error', 'message' => 'Message could not be sent. ' . $e->getMessage()));
        exit;
    }
} else {
    // Form validation failed, send validation errors to AJAX
    echo json_encode(array('status' => 'error', 'message' => 'Validation error. Please check your inputs.'));
    exit;
}
?>
