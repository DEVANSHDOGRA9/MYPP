<?php
$PAGE_TITLE = "Customers";
include_once(__DIR__ . '/adminheader.php');

$query = "SELECT * FROM users_info";
$result = $mysqli->query($query);

// Check if there are results
if ($result === false) {
    die("Error fetching data: " . $mysqli->error);
}
?>

    <div class="container mt-4">
        <h1 class="mb-4">Customers</h1>
        
        <div class="table-container">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <!-- <th>Date of Birth</th>
                        <th>Skills</th> -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
include_once(__DIR__ . '/adminfooter.php');

?>