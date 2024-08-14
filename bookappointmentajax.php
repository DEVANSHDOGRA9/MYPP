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
$errors = [];




// Function to check if a file is a PDF based on its extension
function is_pdf($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    return strtolower($ext) === 'pdf';
}

// Get POST data
if (isset($_POST['name'], $_POST['email'], $_POST['startDate'], $_POST['startTime'], $_POST['endDate'], $_POST['endTime'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $startDate = $_POST['startDate'];
    $startTime = $_POST['startTime'];
    $endDate = $_POST['endDate'];
    $endTime = $_POST['endTime'];
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
$holidayQuery = "SELECT * FROM holiday_info WHERE holiday_date = '$startDate'";
$holidayResult = mysqli_query($mysqli, $holidayQuery);

if (mysqli_num_rows($holidayResult) > 0) {
    $errors[] = 'Appointments cannot be booked on holidays.';
}

// Check for existing bookings
$bookingQuery = "SELECT * FROM booking_info WHERE (booking_start_datetime BETWEEN '$startDatetime' AND '$endDatetime') OR (booking_end_datetime BETWEEN '$startDatetime' AND '$endDatetime') OR (booking_start_datetime <= '$startDatetime' AND booking_end_datetime >= '$endDatetime')";
$bookingResult = mysqli_query($mysqli, $bookingQuery);

if (mysqli_num_rows($bookingResult) > 0) {
    $errors[] = 'The selected time slot is already booked.';
}

// If there are any validation errors, return them
if (!empty($errors)) {
    echo json_encode(['status' => 'error', 'message' => implode(' ', $errors)]);
    exit;
}

// Sanitize inputs just before using them in SQL queries
$name = mysqli_real_escape_string($mysqli, $name);
$email = mysqli_real_escape_string($mysqli, $email);
$startDatetime = mysqli_real_escape_string($mysqli, $startDatetime);
$endDatetime = mysqli_real_escape_string($mysqli, $endDatetime);

// Begin transaction
mysqli_begin_transaction($mysqli);

try {
    // Insert booking info into the database
    $insertBookingQuery = "INSERT INTO booking_info (person_name, email_address, booking_start_datetime, booking_end_datetime, created_on) VALUES ('$name', '$email', '$startDatetime', '$endDatetime', NOW())";
    if (!mysqli_query($mysqli, $insertBookingQuery)) {
        throw new Exception('Error booking appointment: ' . mysqli_error($mysqli));
    }
    $bookingId = mysqli_insert_id($mysqli);

    // Handle file uploads if files are present
    $uploadedFiles = [];

    $maxFiles = 5;
    $maxFileSize = 10 * 1024 * 1024; // 10MB

    // Check if any files were uploaded
    if (!empty($_FILES['documents']['name'][0])) {
        if (count($_FILES['documents']['name']) > $maxFiles) {
            throw new Exception('You can upload a maximum of 5 files.');
        }

        // Initialize fileinfo for MIME type detection
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        foreach ($_FILES['documents']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                if ($_FILES['documents']['size'][$key] > $maxFileSize) {
                    throw new Exception('File size exceeds 10MB limit.');
                }

                // Validate MIME type and file extension
                $mimeType = finfo_file($finfo, $tmp_name);
                $fileName = $_FILES['documents']['name'][$key];
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);

                if ($mimeType !== 'application/pdf' || !is_pdf($fileName)) {
                    throw new Exception('Only PDF files are allowed.');
                }

                // Generate a unique name for the file
                $uniqueName = uniqid('doc_', true) . '.' . $fileExt;
                $filePath = 'uploads/' . $uniqueName;

                if (move_uploaded_file($tmp_name, $filePath)) {
                    $insertDocQuery = "INSERT INTO booking_documents_info (booking_id, document_path) VALUES ('$bookingId', '$filePath')";
                    if (!mysqli_query($mysqli, $insertDocQuery)) {
                        throw new Exception('Error uploading document: ' . mysqli_error($mysqli));
                    }
                    $uploadedFiles[] = $filePath;
                } else {
                    throw new Exception('Error moving uploaded file.');
                }
            } else {
                throw new Exception('File upload error: ' . $_FILES['documents']['error'][$key]);
            }
        }

        // Close the fileinfo resource
        finfo_close($finfo);
    }

    // Send email
    $mail = new PHPMailer(true);
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
    $mail->addAddress('devanshdogra@orientaloutsourcing.com');

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
    
    // Commit transaction if everything is successful
    mysqli_commit($mysqli);
    echo json_encode(['status' => 'success', 'message' => 'Booking and email sent successfully!']);

    // Clean up uploaded files after sending email
    foreach ($uploadedFiles as $filePath) {
        unlink($filePath); // Remove file after sending
    }
} catch (Exception $e) {
    mysqli_rollback($mysqli); // Rollback transaction if an error occurs
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Close the database connection
mysqli_close($mysqli);
?>
