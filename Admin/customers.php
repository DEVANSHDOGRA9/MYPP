<?php
$PAGE_TITLE = "Customers";
include_once(__DIR__ . '/adminheader.php');

// Fetch skills for dropdown
$skills_query = "SELECT DISTINCT skill_id FROM user_skills";
$skills_result = $mysqli->query($skills_query);

$skills = [];
if ($skills_result) {
    while ($row = $skills_result->fetch_assoc()) {
        // Fetch skill names for skill_ids
        $skill_name_query = "SELECT skill_name FROM skills WHERE id = ?";
        $skill_name_stmt = $mysqli->prepare($skill_name_query);
        $skill_name_stmt->bind_param("i", $row['skill_id']);
        $skill_name_stmt->execute();
        $skill_name_result = $skill_name_stmt->get_result();
        if ($skill_name_result) {
            $skill_name_row = $skill_name_result->fetch_assoc();
            $skills[$row['skill_id']] = $skill_name_row['skill_name'];
        }
    }
}

$selected_skill = isset($_GET['skill']) ? $_GET['skill'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$_GET['page'] = $page;
$offset = ($page - 1) * $limit;

// Sorting settings
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], ['first_name', 'last_name', 'email']) ? $_GET['sort_by'] : 'first_name';
$sort_order = isset($_GET['sort_order']) && in_array($_GET['sort_order'], ['ASC', 'DESC']) ? $_GET['sort_order'] : 'ASC';

// Get total number of records
$total_query = "SELECT COUNT(DISTINCT users_info.id) FROM users_info";
$conditions = [];
$bind_types = '';
$bind_values = [];

if ($selected_skill) {
    $conditions[] = "user_skills.skill_id = ?";
    $bind_types .= 'i';
    $bind_values[] = $selected_skill;
}
if ($search_term) {
    $conditions[] = "(users_info.first_name LIKE ? OR users_info.last_name LIKE ? OR users_info.email LIKE ?)";
    $bind_types .= str_repeat('s', 3);
    $search_term_like = "%$search_term%";
    $bind_values = array_merge($bind_values, [$search_term_like, $search_term_like, $search_term_like]);
}

if (!empty($conditions)) {
    $total_query .= " JOIN user_skills ON users_info.id = user_skills.user_id WHERE " . implode(' AND ', $conditions);
}

$total_stmt = $mysqli->prepare($total_query);
if ($bind_types) {
    $total_stmt->bind_param($bind_types, ...$bind_values);
}
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

// Fetch paginated results with sorting and filtering
$query = "SELECT DISTINCT users_info.* FROM users_info";
$conditions = [];
if ($selected_skill || $search_term) {
    $query .= " JOIN user_skills ON users_info.id = user_skills.user_id";
    if ($selected_skill) {
        $conditions[] = "user_skills.skill_id = ?";
    }
    if ($search_term) {
        $conditions[] = "(users_info.first_name LIKE ? OR users_info.last_name LIKE ? OR users_info.email LIKE ?)";
    }
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}
$query .= " ORDER BY $sort_by $sort_order LIMIT $limit OFFSET $offset";

$stmt = $mysqli->prepare($query);
$bind_types = '';
$bind_values = [];

if ($selected_skill) {
    $bind_types .= 'i';
    $bind_values[] = $selected_skill;
}
if ($search_term) {
    $bind_types .= str_repeat('s', 3);
    $search_term_like = "%$search_term%";
    $bind_values = array_merge($bind_values, [$search_term_like, $search_term_like, $search_term_like]);
}

if ($bind_types) {
    $stmt->bind_param($bind_types, ...$bind_values);
}
$stmt->execute();
$result = $stmt->get_result();

// Check if there are results
if ($result === false) {
    die("Error fetching data: " . $mysqli->error);
}

// Determine sort direction for each column
$first_name_sort_order = ($sort_by === 'first_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC';
$last_name_sort_order = ($sort_by === 'last_name' && $sort_order === 'ASC') ? 'DESC' : 'ASC';
$email_sort_order = ($sort_by === 'email' && $sort_order === 'ASC') ? 'DESC' : 'ASC';

// Base URL without sorting and pagination parameters
$base_url = strtok($_SERVER["REQUEST_URI"], '?');
?>
<div class="container mt-4">
    <h1 class="mb-4">Customers</h1>
    <div id="responseMessage" class="mt-3"></div>
    
    <div class="mb-3 d-flex justify-content-between">
        <div class="d-flex">
        <form method="GET" action="" class="form-inline">
    <div class="input-group input-group-md mr-2">
        <input type="text" name="search" class="form-control" placeholder="Search by email or name..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    </div>
    <div class="input-group input-group-md mr-2">
        <select name="skill" class="form-control">
            <option value="">Select Skills</option>
            <?php foreach ($skills as $skill_id => $skill_name) { ?>
                <option value="<?php echo htmlspecialchars($skill_id); ?>" <?php echo $selected_skill == $skill_id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($skill_name); ?>
                </option>
            <?php } ?>
        </select>
    </div>
    <div class="input-group-append">
        <button class="btn btn-primary" type="submit">Search</button>
    </div>
    </form>

        </div>
        <button id="delete_selected" class="btn btn-danger btn-sm" disabled>Delete</button>
    </div>
    
    <div class="table-container">
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select_all">
                    </th>
                    <th>
                        <?php 
                        $temp_get = $_GET;
                        $temp_get['sort_by'] = "first_name";
                        $temp_get['sort_order'] = $first_name_sort_order;
                        ?>
                        <a href="?<?php echo http_build_query($temp_get); ?>" class="text-decoration-none">
                            First Name
                            <?php if ($sort_by === 'first_name') { ?>
                                <i class="fa <?php echo $sort_order === 'ASC' ? 'fa-sort-asc' : 'fa-sort-desc'; ?>"></i>
                            <?php } else { ?>
                                <i class="fa fa-sort"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        <?php 
                        $temp_get = $_GET;
                        $temp_get['sort_by'] = "last_name";
                        $temp_get['sort_order'] = $last_name_sort_order;
                        ?>
                        <a href="?<?php echo http_build_query($temp_get); ?>" class="text-decoration-none">
                            Last Name
                            <?php if ($sort_by === 'last_name') : ?>
                                <i class="fa <?php echo $sort_order === 'ASC' ? 'fa-sort-asc' : 'fa-sort-desc'; ?>"></i>
                            <?php else : ?>
                                <i class="fa fa-sort"></i>
                            <?php endif; ?>
                        </a>
                    </th>
                    <th>
                        <?php 
                        $temp_get = $_GET;
                        $temp_get['sort_by'] = "email";
                        $temp_get['sort_order'] = $email_sort_order;
                        ?>
                        <a href="?<?php echo http_build_query($temp_get); ?>" class="text-decoration-none">
                            Email
                            <?php if ($sort_by === 'email'){ ?>
                                <i class="fa <?php echo $sort_order === 'ASC' ? 'fa-sort-asc' : 'fa-sort-desc'; ?>"></i>
                            <?php  } else {   ?>
                                <i class="fa fa-sort"></i>
                            <?php } ?>
                        </a>
                    </th>
                    <th>
                        Action
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="user-checkbox" data-user-id="<?php echo $row['id']; ?>" data-user-first-name="<?php echo $row['first_name']; ?>">
                        </td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td> 
                            <i data-user-id="<?php echo $row['id']; ?>" id="delete_customer" class="text-danger fas fa-trash fa-sm"></i>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination controls -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-start">
            <?php if ($page > 1) { ?>
                <li class="page-item">
                    <?php 
                    $temp_get = $_GET;
                    $temp_get['page'] = $page - 1;
                    ?>
                    <a class="page-link" href="?<?php echo http_build_query($temp_get); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
            <?php } ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php 
                    $temp_get = $_GET;
                    $temp_get['page'] = $i;
                    ?>
                    <a class="page-link" href="?<?php echo http_build_query($temp_get); ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <li class="page-item">
                    <?php 
                    $temp_get = $_GET;
                    $temp_get['page'] = $page + 1;
                    ?>
                    <a class="page-link" href="?<?php echo http_build_query($temp_get); ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </nav>
</div>

<script>
// JavaScript for handling select all and delete actions
document.getElementById('select_all').addEventListener('click', function() {
    var checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = this.checked;
    }, this);
});

document.getElementById('delete_selected').addEventListener('click', function() {
    var selectedUsers = [];
    document.querySelectorAll('.user-checkbox:checked').forEach(function(checkbox) {
        selectedUsers.push(checkbox.getAttribute('data-user-id'));
    });

    if (selectedUsers.length > 0) {
        if (confirm('Are you sure you want to delete the selected users?')) {
            // Implement the AJAX request to delete users
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_users.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Handle response
                    document.getElementById('responseMessage').innerHTML = xhr.responseText;
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('An error occurred while deleting users.');
                }
            };
            xhr.send('user_ids=' + encodeURIComponent(JSON.stringify(selectedUsers)));
        }
    } else {
        alert('Please select at least one user to delete.');
    }
});
</script>

<?php
include_once(__DIR__ . '/adminfooter.php');
?>
