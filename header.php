<?php 
include_once(__DIR__ . "/config.php");
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php if(!empty($PAGE_TITLE)){ echo $PAGE_TITLE; }else { echo "Oriental Outsourcing"; }?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <link href="<?php echo RESOURCE_URL; ?>/css/validationEngine.jquery.css" rel="stylesheet">
    
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> -->
    <!-- Include Modernizr -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    
    
    <script src="ajax-load.js"></script> <!-- Include the AJAX load script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <!-- CSS for the loader -->
    <style>
        #loader {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.8);
            text-align: center;
        }

        #loader img {
            margin-top: 12%;
        }

        /* CSS for form spacing */
        .form-group {
            margin-bottom: 1.5rem; /* Adjust as needed */
        }
    </style>
    <!-- jQuery -->
    <script src="<?php echo RESOURCE_URL; ?>/js/jquery.validationEngine.min.js"></script>
    <script src="<?php echo RESOURCE_URL; ?>/js/jquery.validationEngine.js"></script>
    <script src="<?php echo RESOURCE_URL; ?>/js/languages/jquery.validationEngine-en.js"></script>
</head>
<body>
     <!-- HTML for the loader -->
     <div id="loader">
        <img src="./lg.gif" alt="Loader">
    </div>

    

    <!-- JavaScript for AJAX loader -->
    <script>
        // Show loader on AJAX start
        $(document).ajaxStart(function() {
            $('#loader').fadeIn();
        });

        // Hide loader on AJAX stop
        $(document).ajaxStop(function() {
            $('#loader').fadeOut();
        });
    </script>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="https://orientaloutsourcing.com/wp-content/uploads/2020/12/logo-dark.png" alt="Oriental Outsourcing Logo" height="30">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="load_ajax nav-link active" aria-current="page" href="./index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="load_ajax nav-link" href="./contact1.php">Contact Us</a>
                    </li>
                    <li class="nav-item">
                    <a class="load_ajax nav-link" href="./book-appointment.php">Book Appointment</a>
                </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Show Profile link if user is logged in -->
                        <li class="nav-item">
                            <a class="load_ajax nav-link" href="./profile.php">Profile</a>
                        </li>
                        <!-- Hide Register and Login links if user is logged in -->
                        <li class="nav-item d-none">
                            <a class="load_ajax nav-link" href="./register1.php">Registration</a>
                        </li>
                        <li class="nav-item d-none">
                            <a class="load_ajax nav-link" href="./login.php">Login</a>
                        </li>
                        <li class="nav-item">
                        <a class="load_ajax nav-link" href="./logout.php">Logout</a>
                    </li>
                    <?php else: ?>
                        <!-- Show Register and Login links if user is not logged in -->
                        <li class="nav-item">
                            <a class="load_ajax nav-link" href="./register1.php">Registration</a>
                        </li>
                        <li class="nav-item">
                            <a class="load_ajax nav-link" href="./login.php">Login</a>
                        </li>
                        <!-- Hide Profile link if user is not logged in -->
                        <li class="nav-item d-none">
                            <a class="load_ajax nav-link" href="./profile.php">Profile</a>
                        </li>
                        
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="mpage_container">
   
<!-- </body>
</html> -->
