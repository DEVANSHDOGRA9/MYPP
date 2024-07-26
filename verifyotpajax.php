<?php
// Start session
session_start();

// Database connection details
include 'config.php'; // Ensure $mysqli is defined in this file

// Check if OTP is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve entered OTP
    $enteredOTP = mysqli_real_escape_string($mysqli, $_POST['otp']); // Use $mysqli

    // Validate OTP
    if (isset($_SESSION['temp_otp']) && $_SESSION['temp_otp'] == $enteredOTP) {
        // OTP matched, update is_email_verified to 1 for the user
        $tempUserId = $_SESSION['temp_user_id'];
        $updateQuery = "UPDATE users_info SET is_email_verified = 1 WHERE id = $tempUserId";

        if (mysqli_query($mysqli, $updateQuery)) { // Use $mysqli
            // Email verification successful
            unset($_SESSION['temp_otp']); // Clear OTP from session
            unset($_SESSION['temp_user_id']); // Clear user ID from session

            // Construct login page link
            $loginPageLink = '<a href="login.php">Click here to login</a>';

            // Output success message
            echo 'Your email is successfully verified. You can login now. ' . $loginPageLink;
        } else {
            // Error updating database
            echo 'Error updating verification status: ' . mysqli_error($mysqli); // Use $mysqli
        }
    } else {
        // Incorrect OTP
        echo 'Invalid OTP. Please try again.';
    }
} else {
    // If not a POST request, respond with error
    http_response_code(405);
    echo "Method not allowed.";
}

// Close the connection
mysqli_close($mysqli); // Use $mysqli
?>
