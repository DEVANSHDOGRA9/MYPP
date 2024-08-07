    <?php
    session_start();
    $PAGE_TITLE = "Admin Profile";
    include 'adminheader.php';

    // include '../config.php';
    if (!isset($_SESSION['admin_id'])) {
        echo "<script> window.location.href ='adminlogin.php'; </script>";
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
        .profile-container { display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-start; }
        .profile-image { flex: 1 1 100%; text-align: center; margin-bottom: 20px; }
        .profile-image img { border-radius: 50%; width: 150px; height: 150px; object-fit: cover; }
        .form-container { flex: 1 1 100%; }
        .skills-section { display: flex; flex-wrap: wrap; gap: 10px; }
        .form-check { margin-right: 15px; }
        #responseDiv { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .required-star { color: red; margin-left: 5px; }
        .error-message { color: red; font-size: 0.875em; margin-top: 0.25em; }
        .profile-heading { margin-bottom: 20px; }
        .change-password-btn { position: absolute; top: 4px; right: 20px; background-color: #007bff; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .change-password-btn:hover { background-color: #0056b3; }
        @media (min-width: 576px) { .profile-image img { width: 150px; height: 150px; } }
        @media (min-width: 768px) { .profile-container { flex-wrap: nowrap; } .profile-image { flex: 0 0 40%; } .form-container { flex: 0 0 60%; } }
    </style>

    <div class="container mt-5" style="position: relative;">
        <button class="change-password-btn" onclick="window.location.href='adminchange_password.php'">Change Password</button>
        <h2 class="profile-heading">Admin Profile</h2>
        <form id="profileForm" method="post" enctype="multipart/form-data">
            <div class="profile-container">
                <div class="profile-image">
                    <?php
                    $profileImage = $profile_image ? 'uploads/' . htmlspecialchars($profile_image) : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';
                    ?>
                    <img id="profileImagePreview" src="<?php echo $profileImage; ?>" alt="Profile Image" class="img-thumbnail rounded-circle">
                    <h3 class="mt-3"><?php echo htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name); ?></h3>
                    <p class="mt-2"><?php echo htmlspecialchars($email); ?></p>
                </div>
                <div class="form-container">
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
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
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
        });
    </script>
    <?php include_once(__DIR__ . '/adminfooter.php'); ?>
