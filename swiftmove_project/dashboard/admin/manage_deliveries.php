<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Process deletion if a GET parameter is provided
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM delivery_orders WHERE order_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: manage_deliveries.php");
        exit;
    }
    $stmt->close();
}

// Process update (RUD Update) from POST submission (from modal form)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_delivery'])) {
    $order_id = intval($_POST['order_id']);
    $rider_id = !empty($_POST['rider_id']) ? intval($_POST['rider_id']) : NULL;
    $status = $_POST['status'];
    $scheduled_delivery_time = !empty($_POST['scheduled_delivery_time']) ? $_POST['scheduled_delivery_time'] : NULL;

    $stmt = $conn->prepare("UPDATE delivery_orders SET rider_id = ?, status = ?, scheduled_delivery_time = ? WHERE order_id = ?");
    $stmt->bind_param("issi", $rider_id, $status, $scheduled_delivery_time, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_deliveries.php");
    exit;
}

// Fetch delivery orders from the database
$query = "SELECT order_id, customer_id, rider_id, pickup_location, dropoff_location, delivery_type, status, order_date, scheduled_delivery_time
          FROM delivery_orders
          ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$deliveries = [];
while ($row = $result->fetch_assoc()) {
    $deliveries[] = $row;
}
$stmt->close();

// Fetch available riders (assumed to be in the 'users' table with role = 'rider')
$riders = [];
$riderQuery = "SELECT user_id, name FROM users WHERE role = 'rider'";
$riderResult = $conn->query($riderQuery);
if ($riderResult && $riderResult->num_rows > 0) {
    while ($row = $riderResult->fetch_assoc()) {
        $riders[] = $row;
    }
}

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Deliveries - Admin Dashboard</title>
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for the deliveries table */
        .deliveries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .deliveries-table th,
        .deliveries-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .deliveries-table th {
            background-color: #f1f8ff;
        }

        /* Status Colors */
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

        .status-cancelled {
            color: #e74c3c;
            font-weight: bold;
        }

        /* Search Bar */
        .search-bar {
            margin-bottom: 1rem;
        }

        .search-bar input {
            width: 100%;
            padding: 0.5rem;
            font-size: 16px;
        }

        .action-buttons button {
            margin-right: 5px;
        }

        /* Enhanced Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: #aaa;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover {
            color: #000;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            text-align: center;
        }

        .modal-content .form-group {
            margin-bottom: 1rem;
        }

        .modal-content label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .modal-content input[type="text"],
        .modal-content input[type="datetime-local"],
        .modal-content select {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .modal-content .btn-group {
            text-align: center;
            margin-top: 1.5rem;
        }

        .modal-content .btn-group button {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin: 0 0.5rem;
            transition: background 0.3s;
        }

        .modal-content .btn-update {
            background: #28a745;
            color: #fff;
        }

        .modal-content .btn-update:hover {
            background: #218838;
        }

        .modal-content .btn-cancel {
            background: #dc3545;
            color: #fff;
        }

        .modal-content .btn-cancel:hover {
            background: #c82333;
        }
    </style>
    <script>
        // Dynamic search filter
        function filterTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let table = document.getElementById("deliveriesTable");
            let trs = table.getElementsByTagName("tr");
            for (let i = 1; i < trs.length; i++) {
                let rowText = trs[i].textContent.toLowerCase();
                trs[i].style.display = rowText.indexOf(input) > -1 ? "" : "none";
            }
        }

        // Modal functionality for editing orders
        function openEditModal(orderId, riderId, status, scheduledTime) {
            document.getElementById("editOrderId").value = orderId;
            document.getElementById("editStatus").value = status;
            document.getElementById("editScheduledTime").value = scheduledTime ? scheduledTime : "";
            document.getElementById("editRider").value = riderId ? riderId : "";
            document.getElementById("editModal").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("editModal").style.display = "none";
        }
        window.onclick = function(event) {
            let modal = document.getElementById("editModal");
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</head>

<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-user-shield"></i> Admin Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Dashboard Overview</a></li>
                <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="manage_deliveries.php" class="active"><i class="fa-solid fa-truck"></i> Manage Deliveries</a></li>
                <li><a href="payment_management.php"><i class="fa-solid fa-wallet"></i> Payment Management</a></li>
                <li><a href="loyalty_rewards.php"><i class="fa-solid fa-gift"></i> Loyalty & Discounts</a></li>
                <li><a href="system_settings.php"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Manage Deliveries</h2>
                <p>Review, assign, update, and delete delivery orders.</p>
            </header>

            <!-- Dynamic Search Bar -->
            <div class="search-bar">
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search by Order ID, Customer ID, Rider ID/Name, Drop-off location...">
            </div>

            <?php if (count($deliveries) > 0): ?>
                <table id="deliveriesTable" class="deliveries-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer ID</th>
                            <th>Rider</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Ordered On</th>
                            <th>Scheduled Delivery</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliveries as $delivery): ?>
                            <tr>
                                <td><?php echo $delivery['order_id']; ?></td>
                                <td><?php echo $delivery['customer_id']; ?></td>
                                <td>
                                    <?php
                                    if ($delivery['rider_id']) {
                                        $riderName = "";
                                        foreach ($riders as $r) {
                                            if ($r['user_id'] == $delivery['rider_id']) {
                                                $riderName = $r['name'] . " (" . $r['user_id'] . ")";
                                                break;
                                            }
                                        }
                                        echo $riderName;
                                    } else {
                                        echo "Unassigned";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($delivery['pickup_location']); ?></td>
                                <td><?php echo htmlspecialchars($delivery['dropoff_location']); ?></td>
                                <td><?php echo $delivery['delivery_type']; ?></td>
                                <td class="status-<?php echo strtolower(str_replace(" ", "_", $delivery['status'])); ?>">
                                    <?php echo ucfirst(str_replace("_", " ", $delivery['status'])); ?>
                                </td>
                                <td><?php echo date("Y-m-d", strtotime($delivery['order_date'])); ?></td>
                                <td>
                                    <?php
                                    if ($delivery['scheduled_delivery_time']) {
                                        echo date("Y-m-d H:i", strtotime($delivery['scheduled_delivery_time']));
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <button onclick="openEditModal('<?php echo $delivery['order_id']; ?>', '<?php echo $delivery['rider_id']; ?>', '<?php echo $delivery['status']; ?>', '<?php echo $delivery['scheduled_delivery_time']; ?>')">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <button onclick="if(confirm('Are you sure to delete this order?')) { window.location.href='manage_deliveries.php?delete_id=<?php echo $delivery['order_id']; ?>' }">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No delivery orders found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Enhanced Edit Delivery Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Delivery Order</h3>
            <form method="post" action="manage_deliveries.php">
                <input type="hidden" id="editOrderId" name="order_id">
                <div class="form-group">
                    <label for="editRider">Assign Rider:</label>
                    <select id="editRider" name="rider_id">
                        <option value="">-- Select Rider --</option>
                        <?php foreach ($riders as $rider): ?>
                            <option value="<?php echo $rider['user_id']; ?>">
                                <?php echo htmlspecialchars($rider['name']) . " (" . $rider['user_id'] . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editStatus">Status:</label>
                    <select id="editStatus" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editScheduledTime">Scheduled Delivery Time:</label>
                    <input type="datetime-local" id="editScheduledTime" name="scheduled_delivery_time">
                </div>
                <input type="hidden" name="update_delivery" value="1">
                <div class="btn-group">
                    <button type="submit" class="btn-update"><i class="fa-solid fa-check"></i> Update Delivery</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()"><i class="fa-solid fa-times"></i> Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>