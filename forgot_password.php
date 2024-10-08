<?php
// Ensure session is started
$PAGE_TITLE = "Forgot Password";
include 'header.php'; // Include header at the start of the file




// Generate and set CSRF token if not already set
// if (empty($_SESSION['csrf_token'])) {
//     $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
// }
// $csrf_token = $_SESSION['csrf_token'];
?>

<!-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> Font Awesome CSS -->
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
    .form-group .required {
      color: red; /* Red color for asterisk */
      margin-left: 0.25rem; /* Space between label and asterisk */
    }
    .form-group .form-control {
      width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>
<body>
  <!-- Main Content -->
  <div class="main-content">
    <div class="form-container">
      <img src="http://localhost/clonnnneee/MYPP/resources/4957136_4957136.jpg" alt="Forgot Password Image">
      <h2>Forgot Password</h2>
      <p>Enter the email address associated with your account and we'll send you a link to reset your password.</p>
      <form id="forgotPasswordForm">
        <div class="form-group mb-3">
          <label for="email">Email:<span class="required">*</span></label>
          <input type="email" class="form-control" id="email" name="email" required>
          <span class="error-message" id="email-error"></span>
        </div>
        <!-- <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>"> -->
        <button type="submit" class="btn btn-primary">Request Password Reset</button>
      </form>
      <!-- Response Div -->
      <div id="response" class="mt-3"></div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'footer.php'; ?>

  <!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>

  <script>
    $(document).ready(function() {
  $("#forgotPasswordForm").validate({
    rules: {
      email: {
        required: true,
        email: true
      }
    },
    messages: {
      email: "Please enter a valid email address"
    },
    highlight: function(element, errorClass, validClass) {
      $(element).addClass("is-invalid");
      $(element).next(".error-message").show();
    },
    unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass("is-invalid");
      $(element).next(".error-message").hide();
    },
    errorElement: 'span',
    errorClass: 'text-danger', // Add this line to style error messages in red
    submitHandler: function(form) {
      $("#response").empty(); // Clear previous responses

      $.ajax({
        url: 'forgot_pass_sendotp.php',
        type: 'POST',
        data: $(form).serialize(),
        dataType: 'json', // Expect JSON response
        success: function(response) {
          if (response.redirect) {
            window.location.href = response.redirect;
          } else if (response.error) {
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
<!-- </body>
</html> -->
