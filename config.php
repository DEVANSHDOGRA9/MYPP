<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project_db";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

define('SITE_URL','http://localhost/CLONNNNEEE/MYPP');
define('RESOURCE_URL', SITE_URL .'/resources');
?>
