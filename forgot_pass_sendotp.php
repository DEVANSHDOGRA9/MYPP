<?php
session_start(); // Start the session

// Include configuration file and PHPMailer
include 'config.php'; // This should include the $conn variable
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct

header('Content-Type: application/json'); // Set the content type to JSON

// Retrieve email from POST request
$email = $_POST['email'];

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

// Initialize response array
$response = array();

try {
    // Prepare and execute query to check if email exists
    $stmt = mysqli_prepare($mysqli, "SELECT email FROM users_info WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Generate unique OTP
        $otp = rand(100000, 999999);
        
        // Store OTP in session
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_email'] = $email;
        
        // PHPMailer configuration
        $mail->isSMTP();
        $mail->Host       = 'em2.pwh-r1.com'; // SMTP server address
        $mail->SMTPAuth   = true;             // SMTP authentication
        $mail->Username   = 'devanshdogra@orientaloutsourcing.com'; // SMTP username
        $mail->Password   = 'Devansh@2024';   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587;              // SMTP port (typically 587 for STARTTLS)
        
        // Sender and recipient settings
        $mail->setFrom('devanshdogra@orientaloutsourcing.com', 'Mailder');
        $mail->addAddress($email); // Add a recipient
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is <b>$otp</b>. Please enter this code on the verification page.";
        
        // Enable verbose debug output
        // $mail->SMTPDebug = 2; // Uncomment for detailed debug output
        
        // Send the email
        $mail->send();
        
        // Set response data
        $response['redirect'] = 'verifyotpforgotpass.php';
    } else {
        // Set error message
        $response['error'] = 'Email not found';
    }
    
    // Close the prepared statement
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    // Set exception error message
    $response['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

// Output response as JSON
echo json_encode($response);
?>
