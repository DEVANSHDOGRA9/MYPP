<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_project_db";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
define('SITE_URL','http://localhost/task9');
define('RESOURCE_URL',SITE_URL . '/resources');
?>

