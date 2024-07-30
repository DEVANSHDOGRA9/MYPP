<?php
// session_start();
$PAGE_TITLE = "Profile";
include 'header.php'; // Ensure this file includes the database connection

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    echo "<script> window.location.href ='login.php'; </script>"; // Redirect to login if user_id is not set
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT first_name, last_name, email, dob, profile_image FROM users_info WHERE id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $first_name, $last_name, $email, $dob, $profile_image);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Set default values if any variables are null
$first_name = $first_name ?? '';
$last_name = $last_name ?? '';
$email = $email ?? '';
$dob = $dob ?? '';
$profile_image = $profile_image ?? '';

// Fetch all skills
$query = "SELECT id, skill_name FROM skills";
$skills_result = mysqli_query($mysqli, $query);

$skills = [];
while ($row = mysqli_fetch_assoc($skills_result)) {
    $skills[] = $row;
}

// Fetch user's skills
$query = "SELECT skill_id FROM user_skills WHERE user_id = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user_skills_result = mysqli_stmt_get_result($stmt);

$user_skills = [];
while ($row = mysqli_fetch_assoc($user_skills_result)) {
    $user_skills[] = $row['skill_id'];
}

mysqli_stmt_close($stmt);
mysqli_close($mysqli);
?>


    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-container {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            gap: 20px;
            align-items: flex-start;
        }
        .profile-image {
            flex: 1 1 100%; /* Take full width on small screens */
            text-align: center;
            margin-bottom: 20px; /* Add margin for better spacing on small screens */
        }
        .profile-image img {
            border-radius: 50%;
            width: 120px; /* Adjust size for smaller screens */
            height: 120px; /* Adjust size for smaller screens */
            object-fit: cover;
        }
        .form-container {
            flex: 1 1 100%; /* Take full width on small screens */
        }
        .skills-section {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .form-check {
            margin-right: 15px;
        }
        #responseDiv {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Media Queries */
        @media (min-width: 576px) {
            .profile-image img {
                width: 150px;
                height: 150px;
            }
        }
        @media (min-width: 768px) {
            .profile-container {
                flex-wrap: nowrap; /* Prevent wrapping on medium screens and larger */
            }
            .profile-image {
                flex: 0 0 40%;
            }
            .form-container {
                flex: 0 0 60%;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="change_password.php" class="btn btn-secondary">Change Password</a>
        </div>
        <form id="profileForm" method="post" enctype="multipart/form-data">
            <div class="profile-container">
                <!-- Left side: Profile Image -->
                <div class="profile-image">
                    <?php
                    $profileImage = $profile_image ? 'uploads/' . htmlspecialchars($profile_image) : 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';
                    ?>
                    <img id="profileImagePreview" src="<?php echo $profileImage; ?>" alt="Profile Image" class="img-thumbnail rounded-circle">
                    <h3 class="mt-3"><?php echo htmlspecialchars($first_name) . ' ' . htmlspecialchars($last_name); ?></h3>
                    <p class="mt-2"><?php echo htmlspecialchars($email); ?></p>
                </div>

                <!-- Right side: Profile Form -->
                <div class="form-container">
                    <div id="responseDiv" class="d-none"></div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="required">First Name:</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="required">Last Name:</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dob">Date of Birth:</label>
                                <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($dob); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="profile_image">Upload Profile Photo:</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image">
                        <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($profile_image); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label>Skills:</label>
                        <div class="skills-section">
                            <?php foreach ($skills as $skill): ?>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="skill_<?php echo htmlspecialchars($skill['id']); ?>" name="skills[]" value="<?php echo htmlspecialchars($skill['id']); ?>"
                                    <?php if (in_array($skill['id'], $user_skills)) echo 'checked'; ?>>
                                    <label class="form-check-label" for="skill_<?php echo htmlspecialchars($skill['id']); ?>"><?php echo htmlspecialchars($skill['skill_name']); ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
    <script>
        $(document).ready(function() {
            $("#profileForm").on("submit", function(event) {
                event.preventDefault(); // Prevent default form submission

                var formData = new FormData(this);

                $.ajax({
                    url: 'update_profile.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        try {
                            var data = response; // Ensure JSON parsing
                            var alertType = data.success ? 'success' : 'danger';
                            var responseMessage = '<div class="alert alert-' + alertType + '">' + data.message + '</div>';
                            
                            // Update response message
                            $('#responseDiv').html(responseMessage).removeClass("d-none");

                            // Update profile image preview if new image is uploaded
                            if (data.success && data.profile_image) {
                                $('#profileImagePreview').attr('src', 'uploads/' + data.profile_image);
                            }

                            // Redirect if specified in the response
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                        } catch (e) {
                            $('#responseDiv').html('<div class="alert alert-danger">An error occurred while processing the response.</div>').removeClass("d-none");
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#responseDiv').html('<div class="alert alert-danger">An error occurred: ' + xhr.status + ' ' + xhr.statusText + '</div>').removeClass("d-none");
                    }
                });
            });
        });
    </script>

<?php include_once(__DIR__ . '/footer.php'); ?>