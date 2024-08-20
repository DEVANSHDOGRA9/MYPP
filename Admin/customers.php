<?php
// session_start();
ob_start();
$PAGE_TITLE = "Customers";
include_once(__DIR__ . '/adminheader.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
                     <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
            <form method="GET" action="" class="ml-2">
                <button class="btn btn-secondary" type="submit">Reset</button>
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
                            <input type="checkbox" class="select_user" data-user-id="<?php echo $row['id']; ?>">
                        </td>
                        <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-user" data-user-id="<?php echo $row['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="<?php echo $page > 1 ? '?page=' . ($page - 1) . '&' . http_build_query(array_merge($_GET, ['page' => $page - 1])) : '#'; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
            <?php } ?>
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="<?php echo $page < $total_pages ? '?page=' . ($page + 1) . '&' . http_build_query(array_merge($_GET, ['page' => $page + 1])) : '#'; ?>">Next</a>
            </li>
        </ul>
    </nav>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const deleteButtons = document.querySelectorAll(".delete-user");
    const deleteSelectedButton = document.getElementById("delete_selected");
    const selectAllCheckbox = document.getElementById("select_all");
    const checkboxes = document.querySelectorAll(".select_user");
    
    deleteButtons.forEach(button => {
        button.addEventListener("click", function() {
            const userId = this.getAttribute("data-user-id");
            if (confirm("Are you sure you want to delete this user?")) {
                deleteUser(userId);
            }
        });
    });

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            toggleDeleteSelectedButton();
        });
    });

    selectAllCheckbox.addEventListener("change", function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });
        toggleDeleteSelectedButton();
    });

    deleteSelectedButton.addEventListener("click", function() {
        const selectedUsers = Array.from(checkboxes).filter(checkbox => checkbox.checked).map(checkbox => ({
            user_id: checkbox.getAttribute("data-user-id")
        }));
        if (selectedUsers.length > 0 && confirm("Are you sure you want to delete selected users?")) {
            deleteUsers(selectedUsers);
        }
    });

    function toggleDeleteSelectedButton() {
        const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        deleteSelectedButton.disabled = !anyChecked;
    }

    function deleteUser(userId) {
        fetch("customer_ajax.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "delete_user",
                user_id: userId,
                csrf_token: "<?php echo $csrf_token; ?>"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function deleteUsers(users) {
        fetch("customer_ajax.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "delete_users",
                users: JSON.stringify(users),
                csrf_token: "<?php echo $csrf_token; ?>"
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(error => console.error("Error:", error));
    }
});
</script>
<?php
include_once(__DIR__ . '/adminfooter.php');
?>
