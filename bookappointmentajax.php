<?php
session_start();
header('Content-Type: application/json'); // Ensure the response is in JSON format
require 'vendor/autoload.php'; // PHPMailer
include 'config.php'; // Database connection

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Validate CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    exit;
}

// Initialize variables for form data and errors
$name = $email = $startDate = $startTime = $endDate = $endTime = "";
$errors = [];

// Function to sanitize input
function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Get POST data
if (isset($_POST['name'], $_POST['email'], $_POST['startDate'], $_POST['startTime'], $_POST['endDate'], $_POST['endTime'])) {
    $name = test_input($_POST['name']);
    $email = test_input($_POST['email']);
    $startDate = test_input($_POST['startDate']);
    $startTime = test_input($_POST['startTime']);
    $endDate = test_input($_POST['endDate']);
    $endTime = test_input($_POST['endTime']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format.';
}

// Concatenate date and time values
$startDatetime = "$startDate $startTime";
$endDatetime = "$endDate $endTime";

// Validate date and time format
$startDateTimeObj = DateTime::createFromFormat('Y-m-d H:i', $startDatetime);
$endDateTimeObj = DateTime::createFromFormat('Y-m-d H:i', $endDatetime);

if (!$startDateTimeObj || !$endDateTimeObj) {
    $errors[] = 'Invalid date and time format.';
} else {
    // Check that end time is after start time
    if ($endDateTimeObj <= $startDateTimeObj) {
        $errors[] = 'End time must be after start time.';
    }

    // Check appointment duration (between 30 minutes and 1 hour)
    $interval = $startDateTimeObj->diff($endDateTimeObj);
    $durationMinutes = ($interval->h * 60) + $interval->i;
    if ($durationMinutes < 30 || $durationMinutes > 60) {
        $errors[] = 'Appointment duration must be between 30 minutes and 1 hour.';
    }

    // Check for past date or time
    $now = new DateTime();
    if ($startDateTimeObj < $now || $endDateTimeObj < $now) {
        $errors[] = 'Cannot book appointments in the past.';
    }

    // Check if the booking falls within working hours (9AM - 6:30PM)
    $workingStartTime = (clone $startDateTimeObj)->setTime(9, 0);
    $workingEndTime = (clone $startDateTimeObj)->setTime(18, 30);

    if ($startDateTimeObj < $workingStartTime || $endDateTimeObj > $workingEndTime) {
        $errors[] = 'Appointments must be between 9AM and 6:30PM.';
    }

    // Check for lunch break (2PM - 2:30PM)
    $lunchStart = (clone $startDateTimeObj)->setTime(14, 0);
    $lunchEnd = (clone $startDateTimeObj)->setTime(14, 30);

    if ($startDateTimeObj < $lunchEnd && $endDateTimeObj > $lunchStart) {
        $errors[] = 'Appointments cannot be scheduled during lunch break (2PM - 2:30PM).';
    }
}

// Check for holidays
$holidayQuery = "SELECT * FROM holiday_info WHERE holiday_date = ?";
$stmt = mysqli_prepare($mysqli, $holidayQuery);
mysqli_stmt_bind_param($stmt, 's', $startDate);
mysqli_stmt_execute($stmt);
$holidayResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($holidayResult) > 0) {
    $errors[] = 'Appointments cannot be booked on holidays.';
}

// Check for existing bookings
$bookingQuery = "SELECT * FROM booking_info WHERE (booking_start_datetime BETWEEN ? AND ?) OR (booking_end_datetime BETWEEN ? AND ?) OR (booking_start_datetime <= ? AND booking_end_datetime >= ?)";
$stmt = mysqli_prepare($mysqli, $bookingQuery);
mysqli_stmt_bind_param($stmt, 'ssssss', $startDatetime, $endDatetime, $startDatetime, $endDatetime, $startDatetime, $endDatetime);
mysqli_stmt_execute($stmt);
$bookingResult = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($bookingResult) > 0) {
    $errors[] = 'The selected time slot is already booked.';
}

// If there are any validation errors, return them
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

// Insert booking info into the database
$insertBookingQuery = "INSERT INTO booking_info (person_name, email_address, booking_start_datetime, booking_end_datetime, created_on) VALUES (?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($mysqli, $insertBookingQuery);
mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $startDatetime, $endDatetime);

if (!mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'error', 'message' => 'Error booking appointment: ' . mysqli_error($mysqli)]);
    exit;
}
$bookingId = mysqli_insert_id($mysqli);

// Handle file uploads
$uploadedFiles = [];
$allowedTypes = ['application/pdf'];
$maxFiles = 5;
$maxFileSize = 10 * 1024 * 1024; // 10MB

if (count($_FILES['documents']['name']) > $maxFiles) {
    echo json_encode(['status' => 'error', 'message' => 'You can upload a maximum of 5 files.']);
    exit;
}

foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
    if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
        if ($_FILES['documents']['size'][$key] > $maxFileSize) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds 10MB limit.']);
            exit;
        }
        if (!in_array($_FILES['documents']['type'][$key], $allowedTypes)) {
            echo json_encode(['status' => 'error', 'message' => 'Only PDF files are allowed.']);
            exit;
        }
        $filePath = 'uploads/' . basename($_FILES['documents']['name'][$key]);
        if (move_uploaded_file($tmp_name, $filePath)) {
            $insertDocQuery = "INSERT INTO booking_documents_info (booking_id, document_path) VALUES (?, ?)";
            $stmt = mysqli_prepare($mysqli, $insertDocQuery);
            mysqli_stmt_bind_param($stmt, 'is', $bookingId, $filePath);
            if (!mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'error', 'message' => 'Error uploading document: ' . mysqli_error($mysqli)]);
                exit;
            }
            $uploadedFiles[] = $filePath;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error moving uploaded file.']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File upload error: ' . $_FILES['documents']['error'][$key]]);
        exit;
    }
}

// Send email
$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host = 'em2.pwh-r1.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'devanshdogra@orientaloutsourcing.com';
    $mail->Password = 'Devansh@2024';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom($email, $name);
    $mail->addAddress('devanshdogra9@gmail.com');

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Appointment Booking';
    $mail->Body = "A new appointment has been booked:<br>
                    Name: $name<br>
                    Email: $email<br>
                    Start Date & Time: $startDatetime<br>
                    End Date & Time: $endDatetime<br>";

    // Attach files
    foreach ($uploadedFiles as $filePath) {
        $mail->addAttachment($filePath);
    }

    $mail->send();
    echo json_encode(['status' => 'success', 'message' => 'Booking and email sent successfully!']);

    // Clean up uploaded files after sending email
    foreach ($uploadedFiles as $filePath) {
        unlink($filePath); // Remove file after sending
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo]);
}

mysqli_close($mysqli);
?>
