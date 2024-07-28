<?php
// session_start(); // Ensure session is started
$PAGE_TITLE = "Login";
include 'header.php';
// Generate and set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
// Set the dynamic page title

// Debug: Log the CSRF token
// error_log("Session CSRF Token in login.php: " . $csrf_token);
?>

<div class="container mt-5">
  <div class="row">
    <!-- Left Column with Image -->
    <div class="col-md-6">
      <!-- <h2>Login</h2>
      <p>Welcome back! Please log in to access your account.</p> -->
      <img src="http://localhost/clonnnneee/MYPP/resources/4957136_4957136.jpg" class="img-fluid" alt="Login Image">
    </div>
    <!-- Right Column with Form -->
    <div class="col-md-6 d-flex align-items-center">
      <div class="p-5 w-100">
        <h2>Login</h2>
        <form id="loginForm">
          <div class="form-group mb-3">
            <label for="email" class="required">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <span class="error-message" id="email-error"></span>
          </div>
          <div class="form-group mb-3">
            <label for="pwd" class="required">Password:</label>
            <div class="input-group">
              <input type="password" class="form-control" id="pwd" name="pwd" required>
              <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                Show
              </button>
            </div>
            <span class="error-message" id="pwd-error"></span>
          </div>
          
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <!-- Forgot Password Link -->
        <div class="mt-3">
          <a href="forgot_password.php">Forgot Password?</a>
        </div>
        <!-- Response Div -->
        <div id="response" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script> -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script> Font Awesome JS -->

<script>
  $(document).ready(function() {
    // Toggle password visibility
    $("#togglePassword").on('click', function() {
      var passwordField = $("#pwd");
      if (passwordField.attr('type') === 'password') {
        passwordField.attr('type', 'text');
        $(this).text('Hide');
      } else {
        passwordField.attr('type', 'password');
        $(this).text('Show');
      }
    });

    $("#loginForm").validate({
      rules: {
        email: {
          required: true,
          email: true
        },
        pwd: {
          required: true,
          minlength: 5
        }
      },
      messages: {
        email: "Please enter a valid email address",
        pwd: "Please enter your password"
      },
      highlight: function(element, errorClass, validClass) {
        $(element).addClass("is-invalid");
        $(element).next(".error-message").show();
      },
      unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass("is-invalid");
        $(element).next(".error-message").hide();
      },
      errorPlacement: function(error, element) {
        if (element.attr("name") == "pwd") {
          error.insertAfter(element.closest(".input-group")).next("#pwd-error");
        } else {
          error.insertAfter(element);
        }
      },
      errorElement: 'span',
      errorClass: 'text-danger', // Add this line to style error messages in red
      submitHandler: function(form) {
        $("#response").empty(); // Clear previous responses

        $.ajax({
          url: 'loginajax.php',
          type: 'POST',
          data: $(form).serialize(),
          success: function(response) {
            // Update the response div with the response message
            if (response === 'success') {
                setTimeout(() => {
                    window.location.href = "profile.php";
                }, 1000);
            
            } else {
              $("#response").html('<div class="alert alert-danger" role="alert">' + response + '</div>');
            }
          }
        });
      }
    });
  });
</script>

<style>
  .error-message {
    color: red; /* Red color for error messages */
    font-size: 0.875em; /* Smaller font size for error messages */
    display: none; /* Hide error messages by default */
  }
  .is-invalid {
    border-color: #dc3545; /* Red border color for invalid inputs */
  }
  .btn-outline-secondary {
    cursor: pointer; /* Pointer cursor for the toggle button */
  }
  .input-group {
    position: relative;
  }
  #togglePassword {
    color:whitesmoke;
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    z-index: 299;
    border: ridge; 
    background: blue; 
    padding: 0.5rem; /* Add padding to center the text */
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #pwd {
    padding-right: 5rem; /* Space for the toggle button */
  }
  .text-danger {
    color: red !important; /* Ensure error messages are styled in red */
  }
</style>

<?php include_once(__DIR__ . '/footer.php'); ?>
