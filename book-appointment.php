<?php
$PAGE_TITLE = "Book Appointment";
include_once 'header.php';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
} ?>

<!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/css/bootstrap.min.css"> -->
<style>
    .instructions {
        color: blue !important; /* Use !important to override other styles */
    }
    .appointment-form {
        margin-top: 50px;
    }
    .form-control-file,
    .form-control {
        display: block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    .required-star {
        color: red;
        margin-left: 5px;
    }
    .error-message {
        color: red;
        font-size: 0.875em;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .form-group {
        flex: 1 1 calc(50% - 1rem); /* Adjust as needed */
    }
</style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <img src="https://orientaloutsourcing.com/images/contact.png" alt="Contact Image" class="img-fluid">
        </div>
        <div class="col-md-8">
            <!-- Alert container -->
            <div id="alert-container"></div>

            <form id="appointmentForm" class="appointment-form">
                <h2>Book Appointment</h2>
                <p>*Fields are mandatory</p>
                <div class="form-group">
                    <label for="name">Name <span class="required-star">*</span></label>
                    <input type="text" class="form-control" id="name" name="name">
                    <span class="error-message" id="name-error"></span>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required-star">*</span></label>
                    <input type="email" class="form-control" id="email" name="email">
                    <span class="error-message" id="email-error"></span>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="startDate">Start Date <span class="required-star">*</span></label>
                        <input type="date" class="form-control" id="startDate" name="startDate">
                        <span class="error-message" id="startDate-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="startTime">Start Time <span class="required-star">*</span></label>
                        <input type="time" class="form-control" id="startTime" name="startTime">
                        <span class="error-message" id="startTime-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date <span class="required-star">*</span></label>
                        <input type="date" class="form-control" id="endDate" name="endDate">
                        <span class="error-message" id="endDate-error"></span>
                    </div>
                    <div class="form-group">
                        <label for="endTime">End Time <span class="required-star">*</span></label>
                        <input type="time" class="form-control" id="endTime" name="endTime">
                        <span class="error-message" id="endTime-error"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="documents">Upload documents (if any)</label>
                    <input type="file" class="form-control" id="documents" name="documents[]" accept="application/pdf" multiple>
                    <small class="form-text text-muted instructions">
                        INSTRUCTIONS: Only PDF files are allowed, max 5 files can be uploaded, max allowed file size is 10MB each.
                    </small>
                    <span class="error-message" id="documents-error"></span>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script> -->
<script>
$(document).ready(function() {
    // Set the minimum date to today for both startDate and endDate fields
    var today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format
    $('#startDate').attr('min', today);
    $('#endDate').attr('min', today);
    
    $('#appointmentForm').on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission
        
        var isValid = true;
        var currentDate = new Date();
        var startDate = new Date($('#startDate').val() + 'T' + $('#startTime').val());
        var endDate = new Date($('#endDate').val() + 'T' + $('#endTime').val());
        var officeOpen = new Date(startDate.toDateString() + ' 09:00:00');
        var officeClose = new Date(startDate.toDateString() + ' 18:30:00');
        var lunchStart = new Date(startDate.toDateString() + ' 14:00:00');
        var lunchEnd = new Date(startDate.toDateString() + ' 14:30:00');
        
        // Clear previous errors and alerts
        $('.error-message').text('');
        $('#alert-container').html('');

        // Validate Name
        if ($('#name').val().trim() === '') {
            $('#name-error').text('Name is required.');
            isValid = false;
        }

        // Validate Email
        var email = $('#email').val().trim();
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === '' || !emailPattern.test(email)) {
            $('#email-error').text('Valid email is required.');
            isValid = false;
        }

        // Validate Dates and Times
        if ($('#startDate').val().trim() === '') {
            $('#startDate-error').text('Start Date is required.');
            isValid = false;
        }
        if ($('#startTime').val().trim() === '') {
            $('#startTime-error').text('Start Time is required.');
            isValid = false;
        }
        if ($('#endDate').val().trim() === '') {
            $('#endDate-error').text('End Date is required.');
            isValid = false;
        }
        if ($('#endTime').val().trim() === '') {
            $('#endTime-error').text('End Time is required.');
            isValid = false;
        }

        // Validate that start time is before end time
        if (startDate >= endDate) {
            $('#startTime-error').text('End time must be after start time.');
            isValid = false;
        }

        // Validate that the appointment is within working hours
        if (startDate < officeOpen || endDate > officeClose || (startDate < lunchEnd && endDate > lunchStart)) {
            $('#startTime-error').text('Appointments must be within working hours (9AM to 6:30PM, excluding 2PM to 2:30PM).');
            isValid = false;
        }

        // Validate minimum and maximum time slot length
        var duration = (endDate - startDate) / (1000 * 60); // duration in minutes
        if (duration < 30 || duration > 60) {
            $('#startTime-error').text('Booking duration must be between 30 minutes and 1 hour.');
            isValid = false;
        }

        // Validate booking date is not today or a past date
        if (startDate.toDateString() === currentDate.toDateString() || startDate < currentDate) {
            $('#startDate-error').text('Cannot book for today or a past date.');
            isValid = false;
        }

        // File Upload Validation
        var files = $('#documents')[0].files;
        if (files.length > 5) {
            $('#documents-error').text('You can upload a maximum of 5 files.');
            isValid = false;
        } else {
            for (var i = 0; i < files.length; i++) {
                if (files[i].size > 10 * 1024 * 1024) {
                    $('#documents-error').text('Each file must be less than 10MB.');
                    isValid = false;
                    break;
                }
            }
        }

        if (isValid) {
            // Initialize FormData and check if it's defined
            var formData = new FormData(this);
            if (formData) {
                console.log('FormData is defined');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }
            } else {
                console.log('FormData is not defined');
            }

            // Perform the AJAX request
            $.ajax({
                url: 'bookappointmentajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json', // Expecting JSON response
                success: function(response) {
                    if (response.status === 'success') {
                        $('#alert-container').html(
                            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            response.message +
                            '</div>'
                        );
                        setTimeout(function() {
                            $('.alert').alert('close');
                        }, 6000);
                        // $('#appointmentForm')[0].reset();
                        $('#documents').val('');
                    } else {
                        $('#alert-container').html(
                            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            response.message +
                            '</div>'
                        );
                       
                        setTimeout(function() {
                            $('.alert').alert('close');
                        }, 6000);
                    }
                         $('#appointmentForm')[0].reset();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#alert-container').html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        'There was an error booking the appointment. ' + textStatus + ': ' + errorThrown +
                        '</div>'
                    );
                    $('#appointmentForm')[0].reset();
                    setTimeout(function() {
                        $('.alert').alert('close');
                    }, 3000);
                }
            });
        }
    });
});
</script>

<?php include_once(__DIR__ . '/footer.php'); ?>
