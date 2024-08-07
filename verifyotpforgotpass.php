<?php
$PAGE_TITLE = "Verify OTP";
include 'header.php'; // Include header at the start of the file

// Generate and set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
if (!isset($_SESSION['otp_email'])) {
  echo "<script>window.location.href='forgot_password.php';</script>";
  exit();
}
?>

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
  .form-group .form-control {
    width: 100%;
    box-sizing: border-box;
  }
  .required {
    color: red;
  }
</style>

<div class="main-content">
  <div class="form-container">
    <img src="http://localhost/clonnnneee/MYPP/resources/4957136_4957136.jpg" alt="Verify OTP Image">
    <h2>Verify OTP</h2>
    <p>Please enter the OTP sent to your email address.</p>
    <form id="verifyOtpForm">
      <div class="form-group mb-3">
        <label for="otp">OTP:<span class="required">*</span></label>
        <input type="text" class="form-control" id="otp" name="otp" required>
        <span class="error-message" id="otp-error"></span>
      </div>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <button type="submit" class="btn btn-primary">Verify OTP</button>
    </form>
    <!-- Response Div -->
    <div id="response" class="mt-3"></div>
  </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script>
  $(document).ready(function() {
    $("#verifyOtpForm").validate({
      rules: {
        otp: {
          required: true,
          minlength: 6, // Assuming OTP is 6 digits long
          maxlength: 6
        }
      },
      messages: {
        otp: "Please enter a valid OTP"
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
          url: 'verifyotpforgotpassajax.php',
          type: 'POST',
          data: $(form).serialize(),
          dataType: 'json',
          success: function(response) {
            // if (response.success) {
            //   window.location.href = 'reset_password.php' setTimeout(2000);// Redirect to reset_password.php if OTP is correct
            // } 
            if (response.success) {
    setTimeout(function() {
        window.location.href = 'reset_password.php';
    }, 500);
}

            else {
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
