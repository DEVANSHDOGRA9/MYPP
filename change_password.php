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

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    .container {
        max-width: 600px;
        margin-top: 50px;
    }
    .password-field-container {
        position: relative;
    }
    .password-toggle-btn {
        position: absolute;
        /* bottom:5px; */
        top: 50px;
        right: 0px;
        transform: translateY(-50%);
        cursor: pointer;
        border: solid;
        background: blue;
    }
    .error {
        color: red;
        font-size: 0.875em; /* Adjust the font size if needed */
    }
    .error-container {
        margin-top: 5px;
    }
</style>
</head>
<body>
    <div class="container">
        <h2><?php echo htmlspecialchars($PAGE_TITLE); ?></h2>
        <form id="changePasswordForm">
            <div class="mb-3 password-field-container">
                <label for="current_password" class="form-label">Current Password:</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
                <button type="button" class="password-toggle-btn" id="toggleCurrentPassword">
                    Show
                </button>
            </div>
            <div class="mb-3 password-field-container">
                <label for="new_password" class="form-label">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
                <button type="button" class="password-toggle-btn" id="toggleNewPassword">
                    Show
                </button>
            </div>
            <div class="mb-3 password-field-container">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                <button type="button" class="password-toggle-btn" id="toggleConfirmPassword">
                    Show
                </button>
            </div>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#changePasswordForm").validate({
                rules: {
                    current_password: "required",
                    new_password: {
                        required: true,
                        minlength: 6
                    },
                    confirm_password: {
                        required: true,
                        minlength: 6,
                        equalTo: "#new_password"
                    }
                },
                messages: {
                    current_password: "Please enter your current password",
                    new_password: {
                        required: "Please enter a new password",
                        minlength: "Your password must be at least 6 characters long"
                    },
                    confirm_password: {
                        required: "Please confirm your new password",
                        minlength: "Your password must be at least 6 characters long",
                        equalTo: "Passwords do not match"
                    }
                },
                submitHandler: function(form) {
                    var formData = $(form).serialize();

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
