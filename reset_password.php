<?php
// session_start(); // Ensure session is started
$PAGE_TITLE = "Reset Password";
include 'header.php'; // Include header at the start of the file
if (!isset($_SESSION['is_otp_verified']) || $_SESSION['is_otp_verified']!==true) {
  // echo "<script>window.location.href='forgot_password.php';</script>";
  exit();
}

// Generate and set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css">
<style>
 body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem; /* Add padding to prevent content from touching edges */
}

.form-container {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

.form-container img {
  width: 150px; /* Adjusted width to shrink the image */
  height: auto;
  margin-bottom: 1rem;
}

.error-message {
  color: red; /* Red color for error messages */
  font-size: 0.875em; /* Smaller font size for error messages */
  display: none; /* Hide error messages by default */
}

.btn-primary {
  background-color: #007bff;
  border-color: #007bff;
}

.btn-primary:hover {
  background-color: #0056b3;
  border-color: #004085;
}

.form-container h2 {
  margin-bottom: 0.5rem;
}

.form-container p {
  margin-bottom: 1rem;
}

.form-group label {
  display: flex;
  justify-content: flex-start;
  font-weight: 600;
}

.form-group .form-control {
  width: 100%;
  box-sizing: border-box;
  padding-right: 60px; /* Add space for the toggle button */
}

.required {
  color: red;
}

.is-invalid + .error-message {
  display: block; /* Display error messages when input is invalid */
}

.error {
  color: red; /* Ensure jQuery Validate uses red color for errors */
  font-size: 0.875em;
}

.password-toggle {
  position: absolute;
  right: 0px;
  top: 25px;
  /* transform: translateY(-50%); */ /* Remove this line if not needed */
  cursor: pointer;
  background: #ccc; /* Adjust background color */
  border: 1px solid #ddd; /* Add border */
  color: #000; /* Text color */
  padding: 5px ; /* Adjust padding for better appearance */
  border-radius: 4px; /* Add border-radius for better appearance */
}


.form-group {
  position: relative; /* Position relative for password toggle */
}

footer {
  background: #f8f9fa;
  padding: 1rem;
  text-align: center;
}

</style>

<div class="main-content">
  <div class="form-container">
    <img src="http://localhost/clonnnneee/MYPP/resources/4957136_4957136.jpg" alt="Reset Password Image">
    <h2>Reset Password</h2>
    <p>Please enter and confirm your new password.</p>
    <form id="resetPasswordForm">
      <div class="form-group ">
        <label for="new_password">New Password:<span class="required">*</span></label>
        <input type="password" class="form-control" id="new_password" name="new_password" required>
        <button type="button" class="password-toggle" onclick="togglePassword('new_password', this)">Show</button>
        <span class="error-message" id="new_password-error"></span>
      </div>
      <div class="form-group mb-3">
        <label for="confirm_password">Confirm Password:<span class="required">*</span></label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)">Show</button>
        <span class="error-message" id="confirm_password-error"></span>
      </div>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
    <div id="response" class="mt-3"></div>
  </div>
</div>


<?php include 'footer.php'; ?>

<!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
  function togglePassword(fieldId, toggleButton) {
  var field = document.getElementById(fieldId);
  if (field.type === "password") {
    field.type = "text";
    toggleButton.textContent = "Hide";
    
  } else {
    field.type = "password";
    toggleButton.textContent = "Show";
  }
}

$(document).ready(function() {
  $("#resetPasswordForm").validate({
    rules: {
      new_password: {
        required: true,
        minlength: 6 // Adjust the minimum length as required
      },
      confirm_password: {
        required: true,
        equalTo: "#new_password" // Ensure the passwords match
      }
    },
    messages: {
      new_password: {
        required: "Please enter your new password",
        minlength: "Your password must be at least 6 characters long"
      },
      confirm_password: {
        required: "Please confirm your new password",
        equalTo: "Passwords do not match"
      }
    },
    errorElement: 'span', // Wrap error messages in <span> tags
    errorClass: 'error', // Apply custom error class
    highlight: function(element, errorClass, validClass) {
      $(element).addClass("is-invalid");
      $(element).next(".error-message").show();
    },
    unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass("is-invalid");
      $(element).next(".error-message").hide();
    },
    submitHandler: function(form) {
      $("#response").empty(); // Clear previous responses

      $.ajax({
        url: 'resetpassajax.php', // Ensure this path is correct
        type: 'POST',
        data: $(form).serialize(),
        success: function(response) {
          response = JSON.parse(response); // Parse the JSON response
          if (response.success) {
            $("#response").html('<div class="alert alert-success" role="alert">' + response.success + '. Redirecting...</div>');
            setTimeout(function() {
              window.location.href = 'login.php'; // Redirect to login page or any other page
            }, 2000);
          } else {
            $("#response").html('<div class="alert alert-danger" role="alert">' + response.error + '</div>');
          }
        },
        error: function(xhr, status, error) {
          $("#response").html('<div class="alert alert-danger" role="alert">An error occurred: ' + error + '</div>');
        }
      });
    }
  });
});

</script>
