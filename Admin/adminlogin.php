<?php
session_start(); // Ensure the session is started

$PAGE_TITLE = "Admin Login";
include_once(__DIR__ . '/../config.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($PAGE_TITLE) ? $PAGE_TITLE : "Oriental Outsourcing"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .ajax-loader-container {
            display: none;
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
            background: #ffffff82;
        }

        .ajax-loader {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            width: 100px;
        }
    </style>
</head>

<body>
    <div class="ajax-loader-container">
        <!-- <img src="/images/ajax-loader.gif" class="ajax-loader" alt="Loading..."> -->
    </div>
    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6">
                                <img src="./assets/images/img1.jpg" alt="Login Image" class="img-fluid">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <h2 class="mb-5">Admin Login</h2>
                                    <form id="adminLogin">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                        <div class="form-group mb-3">
                                            <label for="email">Email: <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="email" name="email">
                                            <small class="text-danger" id="emailErr"></small>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label for="password">Password: <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <small class="text-danger" id="passwordErr"></small>
                                        </div>
                                        <button type="submit" class="btn btn-primary my-3">Login</button>
                                    </form>
                                    <a href="forgot_password.php" class="d-block mt-2">Forgot Password?</a>
                                    <div id="responseMessage" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <script>
            $(document).ready(function() {
                $('#adminLogin').on('submit', function(event) {
                    event.preventDefault(); // Prevent the form from submitting via the browser
                    var formData = new FormData(this);
                    var isValid = true;

                    $('#emailErr').text('');
                    $('#passwordErr').text('');

                    var email = $('#email').val().trim();
                    var password = $('#password').val().trim();

                    if (email === '') {
                        $('#emailErr').text('Email is required');
                        isValid = false;
                    } else if (!validateEmail(email)) {
                        $('#emailErr').text('Invalid email format');
                        isValid = false;
                    }
                    if (password === '') {
                        $('#passwordErr').text('Password is required');
                        isValid = false;
                    }

                    if (isValid) {
                        $.ajax({
                            url: 'adminloginajax.php',
                            type: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            success: function(response) {
                                if (response.status === 'success') {
                                    $('#responseMessage').html('<div class="alert alert-success">' + response.message + ' Redirecting...</div>');
                                    setTimeout(function() {
                                        window.location.href = 'customers.php'; // Redirect to customers.php
                                    }, 2000);
                                } else {
                                    $('#responseMessage').html('<div class="alert alert-danger">' + response.message + '</div>');
                                }
                            },
                            error: function(xhr, status, error) {
                                $('#responseMessage').html('<div class="alert alert-danger">An error occurred: ' + xhr.status + ' ' + xhr.statusText + '</div>');
                            }
                        });
                    }
                });

                function validateEmail(email) {
                    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>
