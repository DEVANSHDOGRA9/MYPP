<?php
// session_start();
$PAGE_TITLE = "Change Password";
include 'header.php';

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
        margin-left: 300px;
    }
    .password-field-container {
        position: relative;
    }
    .password-toggle-btn {
        position: absolute;
        top: 50px;
        right: 0px;
        transform: translateY(-50%);
    }
    .error {
        color: red;
        font-size: 0.875em;
    }
    .error-container {
        margin-top: 5px;
        color:red;
    }
    .required-star {
        color: red;
        margin-left: 5px;
    }
</style>
</head>
<body>
    <div class="container1 ">
        <h2><?php echo htmlspecialchars($PAGE_TITLE); ?></h2>
        <form id="changePasswordForm">
            <div class="mb-3 password-field-container">
                <label for="current_password" class="form-label">Current Password:<span class="required-star">*</span></label>
                <input type="password" class="form-control" id="current_password" name="current_password" >
                <button type="button" class="btn btn-outline-secondary password-toggle-btn" id="toggleCurrentPassword">Show</button>
                <div class="error-container" id="currentPasswordError"></div>
            </div>
            <div class="mb-3 password-field-container">
                <label for="new_password" class="form-label">New Password:<span class="required-star">*</span></label>
                <input type="password" class="form-control" id="new_password" name="new_password" >
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

    <script>
        $(document).ready(function() {
            $("#changePasswordForm").on("submit", function(event) {
                event.preventDefault();
                var valid = true;

                // Clear previous error messages
                $(".error-container").html("");

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
                        url: 'process_change_password.php',
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            if (response === 'success') {
                                alert('Password changed successfully');
                                window.location.href = 'profile.php';
                            } else {
                                alert('An error occurred: ' + response);
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
include 'footer.php';
?>
