<?php
$password = 'hello@2003';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo $hashed_password;
?>

<!-- INSERT INTO `admin_info` (`admin_id`, `first_name`, `last_name`, `email`, `password`) VALUES ('1', 'Devansh', 'Dogra', 'devanshdogra9@gmail.com', '$2y$10$V1yZbODdN.2Vi6F0lx8TBepCPr.rNEb8.Zr2zMCZjHQ7IobmJOh3G'); -->