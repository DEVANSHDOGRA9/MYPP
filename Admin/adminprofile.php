    <?php
    session_start();
    ob_start();  // Start output buffering
    $PAGE_TITLE = "Admin Profile";
    include 'adminheader.php';

    // include '../config.php';
    // ob_start();
    if (!isset($_SESSION['admin_id'])) {
        // echo "<script> window.location.href ='adminlogin.php'; </script>";
        header("Location: adminlogin.php");
        exit();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];



    $admin_id = $_SESSION['admin_id'];

    $query = "SELECT first_name, last_name, email, profile_image, phone, address FROM admin_info WHERE admin_id = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $admin_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $first_name, $last_name, $email, $profile_image, $phone, $address);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    ?>

    
    
    <style>
 .error-message {
    color: red;
    font-size: 0.875em;
    margin-top: 0.25em;
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

.profile-and-password-container {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.profile-image-container {
    flex-shrink: 0;
    margin-right: 20px;
    text-align: center;
}

.content-container {
    flex-grow: 1;
}

.profile-form-container, .password-form-container {
    width: 50%;
    padding: 15px;
}

@media (max-width: 768px) {
    .profile-and-password-container {
        flex-direction: column;
    }

    .profile-image-container {
        position: relative;
        margin-bottom: 20px;
    }
}

.password-field-container {
    position: relative;
}

.password-toggle-btn {
    position: absolute;
    right: 0px;
    top: 30px;
}

#profileImagePreview {
    height: 150px;
    width: 150px;
}
  

    </style>
<div class="container mt-5">
    <div class="profile-and-password-container d-flex">
        <!-- Profile Image Container -->
        <div class="profile-image-container">
            <?php
            $profileImage = $profile_image ? 'uploads/' . htmlspecialchars($profile_image) : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';
            ?>
            <img id="profileImagePreview" src="<?php echo $profileImage; ?>" alt="Profile Image" class="img-thumbnail rounded-circle">
        </div>
        
        <!-- Content Container -->
        <div class="content-container flex-grow-1">
            <div class="row">
                <!-- Admin Profile Section -->
                <div class="col-md-6 profile-form-container pr-3">
                    <h2 class="profile-heading">Admin Profile</h2>
                    <form id="profileForm" method="post" enctype="multipart/form-data">
                        <div id="responseDiv" class="d-none"></div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name:<span class="required-star">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>">
                                    <div id="first_name_error" class="error-message"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name:<span class="required-star">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>">
                                    <div id="last_name_error" class="error-message"></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email:<span class="required-star">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <div id="email_error" class="error-message"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="phone">Phone Number:</label>
                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($phone); ?>" inputmode="numeric">
                            <div id="phone_error" class="error-message"></div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="address">Address:</label>
                            <textarea class="form-control" id="address" name="address"><?php echo htmlspecialchars($address); ?></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label for="profile_image">Upload Profile Photo:</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image">
                            <div id="profile_image_error" class="error-message"></div>
                            <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($profile_image); ?>">
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <button type="submit" class="btn btn-primary">Save Profile</button>
                    </form>
                </div>

                <!-- Password Form Container -->
                <div class="col-md-6 password-form-container pl-3">
                    <h2 class="password-heading">Change Password</h2>
                    <div id="message" class="error-container"></div>
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
            </div>
        </div>
    </div>
</div>
<?php include_once(__DIR__ . '/adminfooter.php'); ?>
<?php ob_end_flush();  // End output buffering and send the output to the browser ?>


    <script>
        $(document).ready(function() {
            $('#phone').on('input', function() {
        var phone = $(this).val();
        // Remove any non-digit characters
        phone = phone.replace(/\D/g, '');
        $(this).val(phone);
    });
            $("#profileForm").on("submit", function(event) {
                event.preventDefault();

                var isValid = true;
                $(".error-message").text("");

                var firstName = $("#first_name").val().trim();
                if (firstName === "") {
                    $("#first_name_error").text("First name is required.");
                    isValid = false;
                }

                var lastName = $("#last_name").val().trim();
                if (lastName === "") {
                    $("#last_name_error").text("Last name is required.");
                    isValid = false;
                }
                var email = $("#email").val().trim();
                if (email === "") {
                    $("#email_error").text("Email is required.");
                    isValid = false;
                }
                var phone = $("#phone").val().trim();
                var phoneRegex = /^[+]?[0-9]{10,15}$/;
                if (phone !== "" && !phoneRegex.test(phone)) {
                    $("#phone_error").text("Invalid phone number format.");
                    isValid = false;
                }

                var fileInput = $('#profile_image')[0];
                var file = fileInput.files[0];
                var allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif'];
                var maxFileSize = 2 * 1024 * 1024;

                if (file) {
                    if (!allowedFileTypes.includes(file.type)) {
                        $('#profile_image_error').text('Invalid file type. Only jpg, png, jpeg, and gif are allowed.');
                        isValid = false;
                    }
                    if (file.size > maxFileSize) {
                        $('#profile_image_error').text('File size exceeds 2 MB limit.');
                        isValid = false;
                    }
                }

                if (!isValid) {
                    return;
                }

                var formData = new FormData(this);

                $.ajax({
                    url: 'adminupdate_profile.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var data = JSON.parse(response);
                        var alertType = data.success ? 'success' : 'danger';
                        var responseMessage = '<div class="alert alert-' + alertType + '">' + data.message + '</div>';

                        $('#responseDiv').html(responseMessage).removeClass("d-none");

                        if (data.success && data.profile_image) {
                            $('#profileImagePreview').attr('src', 'uploads/' + data.profile_image);
                        }

                        if (data.redirect) {
                            window.location.href = data.redirect;
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#responseDiv').html('<div class="alert alert-danger">An error occurred: ' + xhr.status + ' ' + xhr.statusText + '</div>').removeClass("d-none");
                    }
                });
            });

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
    
