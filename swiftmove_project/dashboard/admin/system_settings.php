<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$message = "";

// Process Create System Setting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_setting'])) {
    $setting_key = trim($_POST['setting_key']);
    $setting_value = trim($_POST['setting_value']);

    if (empty($setting_key) || empty($setting_value)) {
        $message = "Both key and value are required.";
    } else {
        // Check if the setting already exists
        $checkStmt = $conn->prepare("SELECT setting_id FROM system_settings WHERE setting_key = ?");
        $checkStmt->bind_param("s", $setting_key);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $message = "Setting key already exists. Please use update option.";
        } else {
            $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->bind_param("ss", $setting_key, $setting_value);
            if ($stmt->execute()) {
                $message = "System setting created successfully.";
            } else {
                $message = "Error creating setting: " . $stmt->error;
            }
            $stmt->close();
        }
        $checkStmt->close();
    }
}

// Process Update System Setting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_setting'])) {
    $setting_id = intval($_POST['setting_id']);
    $setting_value = trim($_POST['setting_value']);

    if (empty($setting_value)) {
        $message = "Setting value cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_id = ?");
        $stmt->bind_param("si", $setting_value, $setting_id);
        if ($stmt->execute()) {
            $message = "System setting updated successfully.";
        } else {
            $message = "Error updating setting: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Process Delete System Setting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_setting'])) {
    $setting_id = intval($_POST['setting_id']);

    $stmt = $conn->prepare("DELETE FROM system_settings WHERE setting_id = ?");
    $stmt->bind_param("i", $setting_id);
    if ($stmt->execute()) {
        $message = "System setting deleted successfully.";
    } else {
        $message = "Error deleting setting: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve all system settings
$query = "SELECT setting_id, setting_key, setting_value, updated_at FROM system_settings ORDER BY updated_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Settings - Admin Dashboard</title>
    <!-- Link to the shared admin dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for System Settings page */
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

        .setting-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        .setting-form h3 {
            margin-bottom: 1rem;
            color: #007bff;
        }

        .setting-form .form-group {
            margin-bottom: 1rem;
        }

        .setting-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .setting-form input[type="text"],
        .setting-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .setting-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .setting-form button {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .setting-form button:hover {
            background-color: #218838;
        }

        .settings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .settings-table th,
        .settings-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .settings-table th {
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
                <li><a href="loyalty_rewards.php"><i class="fa-solid fa-gift"></i> Loyalty & Rewards</a></li>
                <li><a href="system_settings.php" class="active"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>System Settings</h2>
                <p>Manage dynamic settings, terms & conditions, API keys, and more.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Form to Create New System Setting -->
            <div class="setting-form">
                <h3>Create New Setting</h3>
                <form method="post" action="system_settings.php">
                    <div class="form-group">
                        <label for="setting_key">Setting Key:</label>
                        <input type="text" id="setting_key" name="setting_key" placeholder="e.g., terms_conditions" required>
                    </div>
                    <div class="form-group">
                        <label for="setting_value">Setting Value:</label>
                        <textarea id="setting_value" name="setting_value" placeholder="Enter the setting value or content" required></textarea>
                    </div>
                    <button type="submit" name="create_setting"><i class="fa-solid fa-plus"></i> Create Setting</button>
                </form>
            </div>

            <!-- System Settings Table -->
            <?php if (count($settings) > 0): ?>
                <table class="settings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($settings as $setting): ?>
                            <tr>
                                <td><?php echo $setting['setting_id']; ?></td>
                                <td><?php echo htmlspecialchars($setting['setting_key']); ?></td>
                                <td>
                                    <!-- Update Form for setting value -->
                                    <form method="post" action="system_settings.php" style="display:inline-block; width:100%;">
                                        <input type="hidden" name="setting_id" value="<?php echo $setting['setting_id']; ?>">
                                        <textarea name="setting_value" style="width:100%; resize:vertical;"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                                </td>
                                <td><?php echo date("Y-m-d H:i", strtotime($setting['updated_at'])); ?></td>
                                <td>
                                    <button type="submit" name="update_setting" class="action-btn update-btn"><i class="fa-solid fa-save"></i> Update</button>
                                    </form>
                                    <form method="post" action="system_settings.php" onsubmit="return confirm('Are you sure you want to delete this setting?');" style="display:inline-block;">
                                        <input type="hidden" name="setting_id" value="<?php echo $setting['setting_id']; ?>">
                                        <button type="submit" name="delete_setting" class="action-btn delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No system settings found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>