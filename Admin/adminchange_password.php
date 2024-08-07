<?php
session_start();
$PAGE_TITLE = "Change Password";
include 'adminheader.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>

<style>
    .container1 {
        max-width: 600px;
        margin-top: 50px;
        margin-left: auto;
        margin-right: auto;
    }
    .password-field-container {
        position: relative;
    }
    .password-toggle-btn {
        position: absolute;
        top: 50px;
        right: 0;
        transform: translateY(-50%);
    }
    .error {
        color: red;
        font-size: 0.875em;
    }
    .error-container {
        margin-top: 5px;
        color: red;
    }
    .required-star {
        color: red;
        margin-left: 5px;
    }
</style>

<div class="container1">
    <h2><?php echo htmlspecialchars($PAGE_TITLE); ?></h2>
    <div id="message" class="error-container"></div> <!-- Message container -->
    <form id="changePasswordForm">
        <div class="mb-3 password-field-container">
            <label for="current_password" class="form-label">Current Password:<span class="required-star">*</span></label>
            <input type="password" class="form-control" id="current_password" name="current_password">
            <button type="button" class="btn btn-outline-secondary password-toggle-btn" id="toggleCurrentPassword">Show</button>
            <div class="error-container" id="currentPasswordError"></div>
        </div>
        <div class="mb-3 password-field-container">
            <label for="new_password" class="form-label">New Password:<span class="required-star">*</span></label>
            <input type="password" class="form-control" id="new_password" name="new_password">
            <button type="button" class="btn btn-outline-secondary password-toggle-btn" id="toggleNewPassword">Show</button>
            <div class="error-container" id="newPasswordError"></div>
        </div>
        <div class="mb-3 password-field-container">
            <label for="confirm_password" class="form-label">Confirm New Password:<span class="required-star">*</span></label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            <button type="button" class="btn btn-outline-secondary password-toggle-btn" id="toggleConfirmPassword">Show</button>
            <div class="error-container" id="confirmPasswordError"></div>
        </div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script>
    $(document).ready(function() {
        $("#changePasswordForm").on("submit", function(event) {
            event.preventDefault();
            var valid = true;

            // Clear previous error messages
            $(".error-container").html("");
            $("#message").html(""); // Clear previous messages

            // Validate current password
            var currentPassword = $("#current_password").val().trim();
            if (!currentPassword) {
                $("#currentPasswordError").html("Please enter your current password.");
                valid = false;
            }

            // Validate new password
            var newPassword = $("#new_password").val().trim();
            if (!newPassword) {
                $("#newPasswordError").html("Please enter a new password.");
                valid = false;
            } else if (newPassword.length < 6) {
                $("#newPasswordError").html("Your password must be at least 6 characters long.");
                valid = false;
            }

            // Validate confirm password
            var confirmPassword = $("#confirm_password").val().trim();
            if (!confirmPassword) {
                $("#confirmPasswordError").html("Please confirm your new password.");
                valid = false;
            } else if (confirmPassword !== newPassword) {
                $("#confirmPasswordError").html("Passwords do not match.");
                valid = false;
            }

            if (valid) {
                var formData = $(this).serialize();

                $.ajax({
                    url: 'adminprocesschange_password.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response === 'success') {
                            $("#message").html('<div class="alert alert-success">Password changed successfully.</div>');
                            setTimeout(function() {
                                window.location.href = 'adminprofile.php';
                            }, 2000); // Redirect after 2 seconds
                        } else {
                            $("#message").html('<div class="alert alert-danger">An error occurred: ' + response + '</div>');
                        }
                    }
                });
            }
        });

        function togglePasswordVisibility(inputId, buttonId) {
            var input = document.getElementById(inputId);
            var button = document.getElementById(buttonId);
            if (input.type === "password") {
                input.type = "text";
                button.textContent = "Hide";
            } else {
                input.type = "password";
                button.textContent = "Show";
            }
        }

        $("#toggleCurrentPassword").click(function() {
            togglePasswordVisibility("current_password", "toggleCurrentPassword");
        });

        $("#toggleNewPassword").click(function() {
            togglePasswordVisibility("new_password", "toggleNewPassword");
        });

        $("#toggleConfirmPassword").click(function() {
            togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
        });
    });
</script>

<?php
include 'adminfooter.php';
?>
