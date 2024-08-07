<?php 
session_start();
include_once(__DIR__ . '/adminheader.php');
if (!isset($_SESSION['admin_id'])) {
//   header('Location: adminlogin.php');
echo "<script>window.location.href='adminlogin.php';</script>";
  exit();
}
?> 

     
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
       
       <h1 class="h2">Dashboard</h1>  
      </div>
      <div class="container" style="min-height: 650px;">

      </div>
<?php 
include_once(__DIR__ . '/adminfooter.php');

?> 