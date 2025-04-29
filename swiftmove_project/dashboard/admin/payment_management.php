<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$message = "";

// Process Update Payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $payment_status = trim($_POST['payment_status']);

    $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE payment_id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $payment_status, $payment_id);
        if ($stmt->execute()) {
            $message = "Payment updated successfully.";
        } else {
            $message = "Error updating payment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Process Delete Payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_payment'])) {
    $payment_id = intval($_POST['payment_id']);

    $stmt = $conn->prepare("DELETE FROM payments WHERE payment_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $payment_id);
        if ($stmt->execute()) {
            $message = "Payment deleted successfully.";
        } else {
            $message = "Error deleting payment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Retrieve all payments from the database
$query = "SELECT payment_id, order_id, payment_method, payment_status, amount, payment_date, transaction_details 
          FROM payments 
          ORDER BY payment_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Management - Admin Dashboard</title>
    <!-- Link to the shared admin dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for Payment Management */
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

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .payments-table th,
        .payments-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .payments-table th {
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
                <li><a href="payment_management.php" class="active"><i class="fa-solid fa-wallet"></i> Payment Management</a></li>
                <li><a href="loyalty_rewards.php"><i class="fa-solid fa-gift"></i> Loyalty & Discounts</a></li>
                <li><a href="system_settings.php"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Payment Management</h2>
                <p>Manage all payment transactions here.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Payments Table -->
            <?php if (count($payments) > 0): ?>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Order ID</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Amount (LKR)</th>
                            <th>Date</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td><?php echo $payment['order_id']; ?></td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td class="status-<?php echo strtolower($payment['payment_status']); ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </td>
                                <td><?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['transaction_details']); ?></td>
                                <td>
                                    <!-- Update Payment Form -->
                                    <form method="post" action="payment_management.php" style="display:inline-block;">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                        <select name="payment_status">
                                            <option value="paid" <?php echo ($payment['payment_status'] == 'paid') ? "selected" : ""; ?>>Paid</option>
                                            <option value="pending" <?php echo ($payment['payment_status'] == 'pending') ? "selected" : ""; ?>>Pending</option>
                                            <option value="failed" <?php echo ($payment['payment_status'] == 'failed') ? "selected" : ""; ?>>Failed</option>
                                        </select>
                                        <button type="submit" name="update_payment" class="action-btn update-btn"><i class="fa-solid fa-save"></i> Update</button>
                                    </form>
                                    <!-- Delete Payment Form -->
                                    <form method="post" action="payment_management.php" onsubmit="return confirm('Are you sure you want to delete this payment?');" style="display:inline-block;">
                                        <input type="hidden" name="payment_id" value="<?php echo $payment['payment_id']; ?>">
                                        <button type="submit" name="delete_payment" class="action-btn delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No payment records found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>