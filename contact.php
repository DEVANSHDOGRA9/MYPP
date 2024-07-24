<?php 
$PAGE_TITLE = "Contact Us";
include_once(__DIR__ . '/header.php'); 
// Generate a CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<div class="container mt-5">
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <h2>Contact</h2>
            <p>Feel free to reach out to us through any of the methods below:</p>
            <p>Email: <a href="mailto:contact@orientaloutsourcing.com">contact@orientaloutsourcing.com</a></p>
            <p>Phone: <a href="tel:+11234567890">(123) 456-7890</a></p>
            <p>Address: SCO 64-b, City Heart, Kharar, Punjab, India, 140301</p>
            <img src="https://orientaloutsourcing.com/images/contact.png" class="img-fluid" alt="Contact Image">
        </div>
        <!-- Right Column -->
        <div class="col-md-6">
            <h2>Send Us a Message</h2>
            <p><small class="text-muted">* Fields are mandatory</small></p>
            <form id="contactForm" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group mb-3">
                    <label for="name">Name: <span class="text-danger">*</span></label>
                    <input type="text" class="form-control validate[required]" id="name" name="name">
                </div>
                <div class="form-group mb-3">
                    <label for="email">Email: <span class="text-danger">*</span></label>
                    <input type="email" class="form-control validate[required,custom[email]]" id="email" name="email">
                </div>
                <div class="form-group mb-3">
                    <label for="message">Message: <span class="text-danger">*</span></label>
                    <textarea class="form-control validate[required]" id="message" name="message" rows="5"></textarea>
                </div>
                <div class="form-group mb-3">
                    <label for="file">Upload File:</label>
                    <input type="file" class="form-control validate[custom[onlyFileType[docx|pdf|xlsx]],maxSize[5MB]]" id="file" name="file">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
            <div id="responseMessage" class="mt-3"></div>
        </div>
    </div>
</div>

<!-- Custom Script for Validation and Form Submission -->
<script>
    $(document).ready(function() {
        $("#contactForm").validationEngine();

        $('#contactForm').on('submit', function(event) {
            event.preventDefault(); // Prevent the form from submitting via the browser

            if ($("#contactForm").validationEngine('validate')) {
                var formData = new FormData(this);

                $.ajax({
                    url: 'save_contact_ajax.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#responseMessage').html('<div class="alert alert-success">Your message has been successfully sent. We will get back to you shortly.</div>');
                        $('#contactForm')[0].reset();
                        $("#contactForm").validationEngine('hideAll');
                    },
                    error: function() {
                        $('#responseMessage').html('<div class="alert alert-danger">There was an error sending your message. Please try again later.</div>');
                    }
                });
            }
        });
    });
</script>
<?php include_once(__DIR__ . '/footer.php'); ?>