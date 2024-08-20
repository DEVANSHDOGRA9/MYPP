<!-- <div class="mpage_container"> -->
<?php
// Start session
// session_start();
include('config.php');
// Set page title
$PAGE_TITLE = "REGISTRATION";

// Include header
include "header.php";

// Generate a CSRF token if one doesn't already exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<style>
    /* .loader {
        border: 16px solid #f3f3f3; 
        border-top: 16px solid #3498db; 
        border-radius: 50%;
        width: 120px;
        height: 120px;
        animation: spin 2s linear infinite;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1000;
    } */

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* .loader-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none; */
    /* } */
    
</style>

<div class="container mt-5">
    <div class="row">
        <!-- Left Column with Image -->
        <div class="col-md-6">
            <h2>Contact Us</h2>
            <p>Feel free to reach out to us through any of the methods below:</p>
            <p><b>Email:</b> <a href="mailto:contact@orientaloutsourcing.com" style="text-decoration:underline; color: blue;">contact@orientaloutsourcing.com</a></p>
            <p><b>Phone:</b> <a href="tel:+11234567890" style="text-decoration:underline; color: blue;">(123) 456-7890</a></p>
            <p><b>Address:</b> SCO 64-b, City Heart, Kharar, Punjab, India, 140301</p>
            <img src="https://orientaloutsourcing.com/images/contact.png" class="img-fluid" alt="Contact Image">
        </div>
        <!-- Right Column with Registration Form -->
        <div class="col-md-6">
            <div class="registration-form">
                <h2 class="text-left mb-4">Registration Form</h2>
                <p><small class="text-muted">* Fields are mandatory</small></p>
                <div id="responseMessage" class="mt-3"></div>
                <form id="registrationForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div class="form-group">
                        <label for="first_name">First Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name">
                        <div id="first_name_error" class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name">
                        <div id="last_name_error" class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" autocomplete="password">
                        <div id="email_error" class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password (at least 6 characters)<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" aria-describedby="togglePassword" autocomplete="password">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">Show</button>
                            </div>
                        
                        <div id="password_error" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" aria-describedby="toggleConfirmPassword" autocomplete="password">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">Show</button>
                            </div>
                       
                        <div id="confirm_password_error" class="invalid-feedback"></div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
// Include footer
include "footer.php";
?>
<!-- </div> -->
<!-- Loader Wrapper -->
<!-- <div class="loader-wrapper">
    <div class="loader"></div>
</div> -->
<!-- <script src="ajax-load.js"></script> Include the AJAX load script -->

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    $(document).ready (function() {
        $('#registrationForm').on('submit', function(e) {
            e.preventDefault();
            // Show the loader
            // $('.loader-wrapper').show();
            // $('#loader').fadeIn();
            // Reset all errors
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            // Client-side validation
            var isValid = true;
            // Validate first name
            var firstName = $('#first_name').val();
            if (firstName.trim() === '') {
                isValid = false;
                $('#first_name').addClass('is-invalid');
                $('#first_name_error').text('Please enter first name.');
            }
            // Validate last name
            var lastName = $('#last_name').val();
            if (lastName.trim() === '') {
                isValid = false;
                $('#last_name').addClass('is-invalid');
                $('#last_name_error').text('Please enter last name.');
            }
            // Validate email
            var email = $('#email').val();
            if (email.trim() === '') {
                isValid = false;
                $('#email').addClass('is-invalid');
                $('#email_error').text('Please enter a valid email address.');
            } else if (!isValidEmail(email)) {
                isValid = false;
                $('#email').addClass('is-invalid');
                $('#email_error').text('Please enter a valid email address.');
            }
            // Validate password length
            var password = $('#password').val();
            if (password.trim() === '') {
                isValid = false;
                $('#password').addClass('is-invalid');
                $('#password_error').text('Please enter password.');
            } else if (password.length < 6) {
                isValid = false;
                $('#password').addClass('is-invalid');
                $('#password_error').text('The password should be at least 6 characters.');
            }
            // Validate confirm password
            var confirmPassword = $('#confirm_password').val();
            if (confirmPassword.trim() === '') {
                isValid = false;
                $('#confirm_password').addClass('is-invalid');
                $('#confirm_password_error').text('Please confirm your password.');
            } else if (password !== confirmPassword) {
                isValid = false;
                $('#confirm_password').addClass('is-invalid');
                $('#confirm_password_error').text('Passwords do not match.');
            }
            // If all inputs are valid, proceed with Ajax submission
            if (isValid) {
                $.ajax({
                    type: 'POST',
                    url: 'process_registration.php', // Point to process_registration.php for form submission
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.redirect) {
                            $('#responseMessage').html('<div class="alert alert-success">Registration successful! Redirecting... </div>');
                            setTimeout(function(){
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            if (response.first_name) {
                                $('#first_name').addClass('is-invalid');
                                $('#first_name_error').text(response.first_name);
                            }
                            if (response.last_name) {
                                $('#last_name').addClass('is-invalid');
                                $('#last_name_error').text(response.last_name);
                            }
                            if (response.email) {
                                $('#email').addClass('is-invalid');
                                $('#email_error').text(response.email);
                            }
                            if (response.password) {
                                $('#password').addClass('is-invalid');
                                $('#password_error').text(response.password);
                            }
                            if (response.confirm_password) {
                                $('#confirm_password').addClass('is-invalid');
                                $('#confirm_password_error').text(response.confirm_password);
                            }
                            $('#responseMessage').html('<div class="alert alert-danger">Registration failed.'+ response +' Please check the form.</div>');
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.status + ': ' + xhr.statusText + ' - ' + xhr.responseText;
                        $('#responseMessage').html('<div class="alert alert-danger">An error occurred: ' + errorMessage + '. Please try again later.</div>');
                    },
                    // complete: function() {
                    //     $('#loader').fadeOut();
                    // }
                });
            } else {
                // $('.loader-wrapper').hide();
            }
        });

        // Function to validate email format
        function isValidEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            var type = $('#password').attr('type') === 'password' ? 'text' : 'password';
            $('#password').attr('type', type);
            $(this).text(type === 'password' ? 'Show' : 'Hide');
        });

        $('#toggleConfirmPassword').on('click', function() {
            var type = $('#confirm_password').attr('type') === 'password' ? 'text' : 'password';
            $('#confirm_password').attr('type', type);
            $(this).text(type === 'password' ? 'Show' : 'Hide');
        });
    });
</script>


