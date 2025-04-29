<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$message = "";

// Process Create Loyalty Reward
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_reward'])) {
    $user_id = intval($_POST['user_id']);
    $current_tier = trim($_POST['current_tier']);
    $points = intval($_POST['points']);

    $stmt = $conn->prepare("INSERT INTO loyalty_rewards (user_id, current_tier, points) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isi", $user_id, $current_tier, $points);
        if ($stmt->execute()) {
            $message = "Reward created successfully.";
        } else {
            $message = "Error creating reward: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Process Update Loyalty Reward
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_reward'])) {
    $reward_id = intval($_POST['reward_id']);
    $current_tier = trim($_POST['current_tier']);
    $points = intval($_POST['points']);

    $stmt = $conn->prepare("UPDATE loyalty_rewards SET current_tier = ?, points = ? WHERE reward_id = ?");
    if ($stmt) {
        $stmt->bind_param("sii", $current_tier, $points, $reward_id);
        if ($stmt->execute()) {
            $message = "Reward updated successfully.";
        } else {
            $message = "Error updating reward: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Process Delete Loyalty Reward
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_reward'])) {
    $reward_id = intval($_POST['reward_id']);

    $stmt = $conn->prepare("DELETE FROM loyalty_rewards WHERE reward_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $reward_id);
        if ($stmt->execute()) {
            $message = "Reward deleted successfully.";
        } else {
            $message = "Error deleting reward: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Retrieve loyalty rewards records joined with user details
$query = "SELECT lr.reward_id, lr.user_id, lr.current_tier, lr.points, u.name AS user_name, u.email AS user_email
          FROM loyalty_rewards lr 
          JOIN users u ON lr.user_id = u.user_id
          ORDER BY lr.reward_id DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$rewards = [];
while ($row = $result->fetch_assoc()) {
    $rewards[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loyalty & Rewards Management - Admin Dashboard</title>
    <!-- Link to the shared admin dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for Loyalty Rewards Management */
        .message {
            margin: 1rem auto;
            padding: 1rem;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            color: #155724;
            text-align: center;
            max-width: 800px;
        }

        .reward-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        .reward-form h3 {
            margin-bottom: 1rem;
            color: #007bff;
        }

        .reward-form .form-group {
            margin-bottom: 1rem;
        }

        .reward-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .reward-form input[type="text"],
        .reward-form input[type="number"],
        .reward-form select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .reward-form button {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .reward-form button:hover {
            background-color: #218838;
        }

        .rewards-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .rewards-table th,
        .rewards-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .rewards-table th {
            background-color: #f1f8ff;
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
    </style>
</head>

<body>
    <div class="admin-dashboard">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-user-shield"></i> Admin Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Dashboard Overview</a></li>
                <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="manage_deliveries.php"><i class="fa-solid fa-truck"></i> Manage Deliveries</a></li>
                <li><a href="payment_management.php"><i class="fa-solid fa-wallet"></i> Payment Management</a></li>
                <li><a href="loyalty_rewards.php" class="active"><i class="fa-solid fa-gift"></i> Loyalty & Rewards</a></li>
                <li><a href="system_settings.php"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Loyalty & Rewards Management</h2>
                <p>Manage and update loyalty rewards for customers.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Form to Create New Loyalty Reward Record -->
            <div class="reward-form">
                <h3>Create New Reward</h3>
                <form method="post" action="loyalty_rewards.php">
                    <div class="form-group">
                        <label for="user_id">Customer User ID:</label>
                        <input type="number" id="user_id" name="user_id" placeholder="Enter customer user ID" required>
                    </div>
                    <div class="form-group">
                        <label for="current_tier">Current Tier:</label>
                        <select id="current_tier" name="current_tier" required>
                            <option value="Bronze">Bronze</option>
                            <option value="Silver">Silver</option>
                            <option value="Gold">Gold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="points">Points:</label>
                        <input type="number" id="points" name="points" placeholder="Enter points" required>
                    </div>
                    <button type="submit" name="create_reward"><i class="fa-solid fa-plus"></i> Create Reward</button>
                </form>
            </div>

            <!-- Loyalty Rewards Table -->
            <?php if (count($rewards) > 0): ?>
                <table class="rewards-table">
                    <thead>
                        <tr>
                            <th>Reward ID</th>
                            <th>Customer ID</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Tier</th>
                            <th>Points</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rewards as $reward): ?>
                            <tr>
                                <td><?php echo $reward['reward_id']; ?></td>
                                <td><?php echo $reward['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($reward['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($reward['user_email']); ?></td>
                                <td>
                                    <!-- Update Reward Form -->
                                    <form method="post" action="loyalty_rewards.php" style="display:inline-block;">
                                        <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                        <select name="current_tier">
                                            <option value="Bronze" <?php echo ($reward['current_tier'] == 'Bronze') ? "selected" : ""; ?>>Bronze</option>
                                            <option value="Silver" <?php echo ($reward['current_tier'] == 'Silver') ? "selected" : ""; ?>>Silver</option>
                                            <option value="Gold" <?php echo ($reward['current_tier'] == 'Gold') ? "selected" : ""; ?>>Gold</option>
                                        </select>
                                </td>
                                <td>
                                    <input type="number" name="points" value="<?php echo $reward['points']; ?>" style="width:80px;">
                                </td>
                                <td>
                                    <button type="submit" name="update_reward" class="action-btn update-btn"><i class="fa-solid fa-save"></i> Update</button>
                                    </form>
                                    <form method="post" action="loyalty_rewards.php" onsubmit="return confirm('Are you sure you want to delete this reward?');" style="display:inline-block;">
                                        <input type="hidden" name="reward_id" value="<?php echo $reward['reward_id']; ?>">
                                        <button type="submit" name="delete_reward" class="action-btn delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No loyalty rewards found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>