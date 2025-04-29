<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a rider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'rider') {
    header("Location: ../../auth/login.php");
    exit;
}

$riderId = $_SESSION['user_id'];
$message = "";

// Process Order Status Update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['new_status']);

    // Update the order's status
    $stmt = $conn->prepare("UPDATE delivery_orders SET status = ? WHERE order_id = ? AND rider_id = ?");
    $stmt->bind_param("sii", $new_status, $order_id, $riderId);
    if ($stmt->execute()) {
        $message = "Order #$order_id updated successfully.";
    } else {
        $message = "Error updating order: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve assigned orders for this rider (e.g., where status is pending, confirmed, in_transit)
$query = "SELECT order_id, customer_id, pickup_location, dropoff_location, delivery_type, status, order_date 
          FROM delivery_orders 
          WHERE rider_id = ? 
          ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assigned Orders - Rider Dashboard</title>
    <!-- Link to the rider dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/rider-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styles for the orders table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .orders-table th,
        .orders-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .orders-table th {
            background-color: #f1f8ff;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .status-confirmed {
            color: #28a745;
            font-weight: bold;
        }

        .status-in_transit {
            color: #17a2b8;
            font-weight: bold;
        }

        .status-delivered {
            color: #6c757d;
            font-weight: bold;
        }

        /* Style for the inline update form */
        .update-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .update-form select {
            padding: 0.3rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .update-form button {
            padding: 0.3rem 0.75rem;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .update-form button:hover {
            background-color: #0056b3;
        }

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
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-motorcycle"></i> Rider Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                <li><a href="assigned_orders.php" class="active"><i class="fa-solid fa-clipboard-list"></i> Assigned Orders</a></li>
                <li><a href="earnings.php"><i class="fa-solid fa-wallet"></i> Earnings</a></li>
                <li><a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Assigned Orders</h2>
                <p>Review and update the status of your current assigned deliveries below.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (count($orders) > 0): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Ordered On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['pickup_location']); ?></td>
                                <td><?php echo htmlspecialchars($order['dropoff_location']); ?></td>
                                <td><?php echo $order['delivery_type']; ?></td>
                                <td class="status-<?php echo strtolower(str_replace(" ", "_", $order['status'])); ?>">
                                    <?php echo ucfirst(str_replace("_", " ", $order['status'])); ?>
                                </td>
                                <td><?php echo date("Y-m-d", strtotime($order['order_date'])); ?></td>
                                <td>
                                    <!-- Only allow update if the order is not yet delivered -->
                                    <?php if ($order['status'] !== 'delivered'): ?>
                                        <form method="post" action="assigned_orders.php" class="update-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                            <select name="new_status" required>
                                                <?php
                                                // Allowed statuses for update.
                                                $allowedStatuses = [
                                                    'pending' => 'Pending',
                                                    'confirmed' => 'Confirmed',
                                                    'in_transit' => 'In Transit',
                                                    'delivered' => 'Delivered'
                                                ];
                                                foreach ($allowedStatuses as $statusKey => $statusLabel):
                                                ?>
                                                    <option value="<?php echo $statusKey; ?>" <?php if ($order['status'] == $statusKey) echo 'selected'; ?>>
                                                        <?php echo $statusLabel; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_order"><i class="fa-solid fa-save"></i> Update</button>
                                        </form>
                                    <?php else: ?>
                                        <em>No action</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No assigned orders found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>