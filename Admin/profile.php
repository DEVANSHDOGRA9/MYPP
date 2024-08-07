<?php
session_start();
$PAGE_TITLE = "Admin Profile";
include 'adminheader.php'; // Include your database connection file
if (!isset($_SESSION['admin_id'])) {
    // header('Location: login.php');
      echo "<script>window.location.href='adminlogin.php';</script>"
    exit();
}

// // Fetch admin data
// $admin_id = $_SESSION['admin_id'];
// $query = "SELECT * FROM admin_info WHERE admin_id = $admin_id";
// $result = mysqli_query($mysqli, $query);
// $admin = mysqli_fetch_assoc($result);

// $profile_image = !empty($admin['profile_image']) ? 'uploads/' . htmlspecialchars($admin['profile_image']) : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';


$admin_id = $_SESSION['admin_id'];

$query = "SELECT first_name, last_name, email, profile_image, phone, address FROM admin_info WHERE admin_id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $admin_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $first_name, $last_name, $email, $profile_image, $phone, $address);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$first_name = $first_name ?? '';
$last_name = $last_name ?? '';
$email = $email ?? '';
$profile_image = $profile_image ?? '';
$phone = $phone ?? '';
$address = $address ?? '';



// CSRF Token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Use Bootstrap from a CDN -->
<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
<!-- Include jQuery from CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
     .required-star {
        color: red;
        margin-left: 5px;
    }
    /* .profile-icon {
        width: 10px;
        height: 10px;
        border-radius: 50px;
        background-size: cover;
        background-position: center;
        display: inline-block;
    } */
    .profile-container .profile-image img {
    width: 80px; /* Adjust the width as needed */
    height: 80px; /* Adjust the height as needed */
    object-fit: cover; /* Ensures the image covers the element while preserving aspect ratio */
    border-radius: 50%; /* Keeps the image rounded */
}

    .bordered-section {
        border: 1px solid #dee2e6;
        padding: 20px;
        border-radius: 5px;
        background-color: #f8f9fa;
    }
    .form-header {
        font-size: 24px;
        margin-bottom: 20px;
    }
    .error-message {
        color: red;
        font-size: 0.875em;
        margin-top: 0.25em;
    }
</style>
<script>
    $(document).ready(function () {
        $('#profileForm').submit(function (e) {
            e.preventDefault();
            if (validateProfileForm()) {
                $.ajax({
                    url: 'profileajax.php',
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        alert(response);
                    }
                });
            }
        });

        $('#passwordForm').submit(function (e) {
            e.preventDefault();
            if (validatePasswordForm()) {
                $.ajax({
                    url: 'profileajax.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        alert(response);
                    }
                });
            }
        });

        function validateProfileForm() {
            let isValid = true;

            // Validate First Name
            if ($('#firstName').val().trim() === '') {
                $("#first_name_error").text("First name is required.");
                isValid = false;
            }

            // Validate Last Name
            if ($('#lastName').val().trim() === '') {
                $("#last_name_error").text("Last name is required.");
                isValid = false;
            }

            // Validate Email
            const email = $('#email').val().trim();
            if (email === '') {
                $("#email_error").text("Email is required.");
                isValid = false;
            } else if (!validateEmail(email)) {
                alert('Invalid Email format');
                isValid = false;
            }

            // Validate Phone Number
            const phone = $('#phone').val().trim();
            if (phone !== '' && !validatePhoneNumber(phone)) {
                $("#phone_error").text("Invalid phone number format.");
                isValid = false;
            }

            // Validate Profile Image
            const profilePhoto = $('#profilePhoto')[0].files[0];
            if (profilePhoto) {
                const validImageTypes = ['image/gif', 'image/jpeg', 'image/png'];
                if ($.inArray(profilePhoto.type, validImageTypes) === -1) {
                    alert('Invalid Profile Image format');
                    isValid = false;
                } else if (profilePhoto.size > 2 * 1024 * 1024) { // 2 MB
                    alert('Profile Image size should be less than 2MB');
                    isValid = false;
                }
            }

            return isValid;
        }

        function validatePasswordForm() {
            let isValid = true;

            // Validate Current Password
            if ($('#currentPassword').val().trim() === '') {
                alert('Current Password is required');
                isValid = false;
            }

            // Validate New Password
            if ($('#newPassword').val().trim() === '') {
                alert('New Password is required');
                isValid = false;
            }

            // Validate Confirm New Password
            if ($('#confirmNewPassword').val().trim() === '') {
                alert('Confirm New Password is required');
                isValid = false;
            } else if ($('#newPassword').val() !== $('#confirmNewPassword').val()) {
                alert('New Password and Confirm New Password do not match');
                isValid = false;
            }

            return isValid;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePhoneNumber(phone) {
            const re = /^[0-9]{10}$/;
            return re.test(phone);
        }
    });
</script>

<div class="container mt-5">
    <div class="row">
        <!-- Profile Form -->
        <div class="col-md-6">
            <div class="profile-container">
                <div class="profile-image">
                    <img id="profileImagePreview" src="<?php echo $profile_image; ?>" alt="Profile Image" class="img-thumbnail rounded-circle">
                </div>
            </div>
            <div class="form-header">Admin Profile</div>
            <div class="bordered-section">
                
                <form id="profileForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="form-group">
                        <label for="firstName">First Name<span class="required-star">*</span></label>
                        <input type="text" name="first_name" id="firstName" class="form-control" value="<?php echo htmlspecialchars($first_name); ?>">
                        <div id="first_name_error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name<span class="required-star">*</span></label>
                        <input type="text" name="last_name" id="lastName" class="form-control" value="<?php echo htmlspecialchars($last_name); ?>">
                        <div id="last_name_error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email<span class="required-star">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                        <!-- <div id="_error" class="error-message"></div> -->
                        <div id="email_error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                        <div id="phone_error" class="error-message"></div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" class="form-control" value="<?php echo htmlspecialchars($address); ?>">
                    </div>
                    <div class="form-group">
                        <label for="profilePhoto">Upload Profile Photo</label>
                        <input type="file" name="profile_photo" id="profilePhoto" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </form>
            </div>
        </div>
        <!-- Change Password Form -->
        <div class="col-md-6">
            <div class="bordered-section">
                <div class="form-header">Change Password</div>
                <form id="passwordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="change_password">
                    <div class="form-group">
                        <label for="currentPassword">Current Password<span class="required-star">*</span></label>
                        <input type="password" name="current_password" id="currentPassword" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password<span class="required-star">*</span></label>
                        <input type="password" name="new_password" id="newPassword" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="confirmNewPassword">Confirm New Password<span class="required-star">*</span></label>
                        <input type="password" name="confirm_new_password" id="confirmNewPassword" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'adminfooter.php'; ?>
