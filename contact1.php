<?php
$PAGE_TITLE = "CONTACT US";
include_once 'header.php';
// session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} ?>
<!-- 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title> -->

    <style>
        .required::after {
            content: " *";
            color: red;
        }
        .error-message {
            color: red;
            font-size: 0.875em;
        }
        

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <h2>Contact Us</h2>
                <p>Feel free to reach out to us through any of the methods below:</p>
                <p><strong>Email:</strong> <a href="mailto:contact@orientaloutsourcing.com">contact@orientaloutsourcing.com</a></p>
                <p><strong>Phone:</strong> <a href="tel:+1234567890">(123) 456-7890</a></p>
                <p><strong>Address:</strong> SCO 64-b, City Heart, Kharar, Punjab, India, 140301</p>
                <img src="https://orientaloutsourcing.com/images/contact.png" class="img-fluid" alt="Contact Image">
            </div>
            <!-- Right Column -->
            <div class="col-md-6">
                <h2>Send Us a Message</h2>
                <p><small class="text-muted">* Fields are mandatory</small></p>
                <div id="form-message"></div>
                
                <form id="contactForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group mb-3">
                        <label for="name" class="required">Name</label>
                        <input type="text" class="form-control validate[required]" id="name" name="name" value="">
                        <span class="error-message" id="name-error"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="email" class="required">Email</label>
                        <input type="email" class="form-control validate[required,custom[email]]" id="email" name="email" value="">
                        <span class="error-message" id="email-error"></span>
                    </div>
                    <div class="form-group mb-3">
                        <label for="message" class="required">Message</label>
                        <textarea class="form-control validate[required]" id="message" name="message" rows="5"></textarea>
                        <span class="error-message" id="message-error"></span>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="file" class="mb-2">Upload File (PDF, DOCX, XLSX)</label>
                        <br>
                        <input type="file" class="form-control-file" id="file" name="file">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- <div id="loader" ></div> -->

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <script>
        $(document).ready(function() {
            // Initialize jQuery Validation Engine
            $("#contactForm").validationEngine({
                promptPosition: 'topLeft', // Position of the error prompts
                scroll: false, // Scroll to the first error
                autoHidePrompt: true, // Auto-hide prompts
                autoHideDelay: 5000 // Auto-hide delay time in milliseconds
            });

            // Form submission handling
            $('#contactForm').submit(function(e) {
                e.preventDefault(); // Prevent form submission

                // Clear previous error messages
                $('.error-message').html('');

                // Show loader before AJAX request starts
                // $('#loader').show();

                // Validate form data using jQuery Validation Engine
                if ($("#contactForm").validationEngine('validate')) {
                    var formData = new FormData(this);

                    // Submit form via AJAX
                    $.ajax({
                        type: 'POST',
                        url: 'save_contact_ajax1.php', // Adjust URL as needed
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status == 'success') {
                                $('#form-message').html('<div class="alert alert-success" role="alert">' + response.message + '</div>');
                                $('#contactForm')[0].reset(); // Clear form
                            } else {
                                $('#form-message').html('<div class="alert alert-danger" role="alert">' + response.message + '</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            $('#form-message').html('<div class="alert alert-danger" role="alert">There was an error while submitting the form.</div>');
                        },
                        // complete: function() {
                        //     // Hide loader after AJAX request completes
                        //     $('#loader').hide();
                        // }
                    });
                }
                // else {
                //     $('#loader').hide();
                // }
            });
        });
    </script>
    <?php include_once(__DIR__ . '/footer.php'); ?>
<!-- </body>
</html> -->
