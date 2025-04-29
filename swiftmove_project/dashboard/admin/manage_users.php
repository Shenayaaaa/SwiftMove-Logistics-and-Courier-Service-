<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Initialize message variable to capture deletion or update errors
$message = "";

// Process POST requests for update or delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        $userId = intval($_POST['user_id']);
        $newRole = $_POST['new_role'];
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        if (!$stmt) {
            $message = "Update Error: " . $conn->error;
        } else {
            $stmt->bind_param("si", $newRole, $userId);
            if (!$stmt->execute()) {
                $message = "Update Execution Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_user'])) {
        $userId = intval($_POST['user_id']);
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if (!$stmt) {
            $message = "Delete Error: " . $conn->error;
        } else {
            $stmt->bind_param("i", $userId);
            if (!$stmt->execute()) {
                $message = "Delete Execution Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Process GET parameters for search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$filter = isset($_GET['filter']) ? $_GET['filter'] : "All";

// Build SQL query dynamically
$query = "SELECT user_id, name, email, phone, role, created_at FROM users WHERE 1 ";
$params = [];
$types = "";
if (!empty($search)) {
    $query .= "AND (name LIKE ? OR email LIKE ?) ";
    $searchParam = "%" . $search . "%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}
if ($filter != "All") {
    $query .= "AND role = ? ";
    $params[] = $filter;
    $types .= "s";
}
$query .= "ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Users - Admin Dashboard</title>
    <!-- Link to the dedicated admin dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <style>
        /* Additional styles for the search/filter form and table */
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-form input[type="text"] {
            padding: 5px;
            flex: 1;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .search-form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ccc;
        }

        table th {
            background-color: #f0f8ff;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: background 0.3s ease;
        }

        .update-btn {
            background-color: #007bff;
            color: #fff;
        }

        .update-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #ffdddd;
            border: 1px solid #ff5c5c;
            border-radius: 4px;
            color: #333;
        }
    </style>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>

<body>
    <div class="admin-dashboard">
        <!-- Left Sidebar Navigation -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Dashboard Overview</a></li>
                <li><a href="manage_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="manage_deliveries.php"><i class="fa-solid fa-truck"></i> Manage Deliveries</a></li>
                <li><a href="payment_management.php"><i class="fa-solid fa-wallet"></i> Payment Management</a></li>
                <li><a href="loyalty_rewards.php"><i class="fa-solid fa-gift"></i> Loyalty & Discounts</a></li>
                <li><a href="system_settings.php"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </div>
        <!-- Main Content Area -->
        <div class="main-content">
            <h1>Manage Users</h1>
            <?php if (!empty($message)) {
                echo "<div class='message'>$message</div>";
            } ?>
            <!-- Dynamic Search and Filter Form -->
            <form class="search-form" method="get" action="manage_users.php">
                <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                <select name="filter">
                    <option value="All" <?php echo ($filter == "All") ? "selected" : ""; ?>>All</option>
                    <option value="customer" <?php echo ($filter == "customer") ? "selected" : ""; ?>>Customers</option>
                    <option value="admin" <?php echo ($filter == "admin") ? "selected" : ""; ?>>Admin</option>
                    <option value="rider" <?php echo ($filter == "rider") ? "selected" : ""; ?>>Riders</option>
                </select>
                <button type="submit">Search</button>
            </form>
            <!-- Users Table -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <!-- Update Role Form -->
                                    <form method="post" action="manage_users.php" style="display:inline-block;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <select name="new_role">
                                            <option value="customer" <?php echo ($user['role'] == 'customer') ? "selected" : ""; ?>>Customer</option>
                                            <option value="rider" <?php echo ($user['role'] == 'rider') ? "selected" : ""; ?>>Rider</option>
                                            <option value="admin" <?php echo ($user['role'] == 'admin') ? "selected" : ""; ?>>Admin</option>
                                        </select>
                                        <button type="submit" name="update_user" class="action-btn update-btn">Update</button>
                                    </form>
                                </td>
                                <td><?php echo date("Y-m-d", strtotime($user['created_at'])); ?></td>
                                <td>
                                    <!-- Delete User Form -->
                                    <form method="post" action="manage_users.php" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline-block;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <button type="submit" name="delete_user" class="action-btn delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>