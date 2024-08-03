<?php 
$PAGE_TITLE = "Verify OTP";
include "header.php";
// if (!isset($_SESSION['is_otp_verified'])) {
//   echo "<script>window.location.href='forgot_password.php';</script>";
//   exit();
// } 
?>

<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-color: #f8f9fa;
    }
    .content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .container1 {
        height: 100%;
        width: 100%;
    }
    .verification-form {
        padding: 50px; /* Adjust padding to increase vertical height */
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px; /* Set a maximum width for the form box */
        overflow: hidden; /* Prevent content overflow */
        position: relative;
    }
    @media (max-width: 768px) {
        .verification-form {
            padding: 30px; /* Adjust padding for smaller screens */
        }
    }
    .error {
        color: red;
        font-size: 0.875em;
    }
    .required-star {
        color: red;
        margin-left: 5px;
    }
    .alert {
        margin-top: 15px;
        position: absolute;
        bottom: 20px; /* Position the message 20px above the bottom */
        width: 100%;
        left: 0;
        right: 0;
        padding: 10px 20px;
    }
</style>
</head>
<body>
    <div class="content">
        <div class="container1">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="verification-form">
                        <h2 class="text-center mb-4">Verify OTP</h2>
                        <form id="otpVerificationForm">
                            <div class="form-group">
                                <label for="otp">Enter OTP<span class="required-star">*</span></label>
                                <input type="text" class="form-control" id="otp" name="otp">
                                <div class="error" id="otpError"></div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Verify OTP</button>
                        </form>
                        <div id="responseMessage" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#otpVerificationForm').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                var valid = true;

                // Clear previous error messages
                $("#otpError").html("");

                // Validate OTP field
                var otp = $("#otp").val().trim();
                if (!otp) {
                    $("#otpError").html("Please enter the OTP.");
                    valid = false;
                } else if (isNaN(otp)) {
                    $("#otpError").html("OTP must be a number.");
                    valid = false;
                } else if (otp.length !== 6) {
                    $("#otpError").html("OTP must be 6 digits long.");
                    valid = false;
                }

                if (valid) {
                    var formData = $(this).serialize(); // Serialize form data

                    $.ajax({
                        url: 'verifyotpajax.php', // The PHP script that handles OTP verification
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                          $('#responseMessage').html(response);
                          // $('#responseMessage').html('<div class="alert alert-success">OTP successfully verified!</div>');
                          $('#otpVerificationForm')[0].reset(); // Clear the form
                        },
                        error: function(xhr, status, error) {
                            $('#responseMessage').html('<div class="alert alert-danger">An error occurred: ' + error + '</div>');
                        }
                    });
                }
            });
        });
    </script>

<?php include 'footer.php'; ?>
