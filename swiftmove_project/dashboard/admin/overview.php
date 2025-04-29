<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// Query total number of users
$userQuery = "SELECT COUNT(*) AS userCount FROM users";
$userResult = $conn->query($userQuery);
$userCount = ($userResult && $row = $userResult->fetch_assoc()) ? $row['userCount'] : 0;

// Query total number of customers
$customerQuery = "SELECT COUNT(*) AS customerCount FROM users WHERE role = 'customer'";
$customerResult = $conn->query($customerQuery);
$customerCount = ($customerResult && $row = $customerResult->fetch_assoc()) ? $row['customerCount'] : 0;

// Query total number of riders
$riderQuery = "SELECT COUNT(*) AS riderCount FROM users WHERE role = 'rider'";
$riderResult = $conn->query($riderQuery);
$riderCount = ($riderResult && $row = $riderResult->fetch_assoc()) ? $row['riderCount'] : 0;

// Query total number of deliveries
$deliveryQuery = "SELECT COUNT(*) AS deliveryCount FROM delivery_orders";
$deliveryResult = $conn->query($deliveryQuery);
$deliveryCount = ($deliveryResult && $row = $deliveryResult->fetch_assoc()) ? $row['deliveryCount'] : 0;

// Query pending deliveries
$pendingDeliveryQuery = "SELECT COUNT(*) AS pendingCount FROM delivery_orders WHERE status = 'pending'";
$pendingResult = $conn->query($pendingDeliveryQuery);
$pendingCount = ($pendingResult && $row = $pendingResult->fetch_assoc()) ? $row['pendingCount'] : 0;

// Query completed deliveries (delivered)
$completedDeliveryQuery = "SELECT COUNT(*) AS completedCount FROM delivery_orders WHERE status = 'delivered'";
$completedResult = $conn->query($completedDeliveryQuery);
$completedCount = ($completedResult && $row = $completedResult->fetch_assoc()) ? $row['completedCount'] : 0;

// Query total payments (sum of paid amounts) in LKR
$paymentQuery = "SELECT SUM(amount) AS totalPayments FROM payments WHERE payment_status = 'paid'";
$paymentResult = $conn->query($paymentQuery);
$totalPayments = ($paymentResult && $row = $paymentResult->fetch_assoc()) ? $row['totalPayments'] : 0;

// Query total commissions given to drivers (20% of total paid amounts for delivered orders)
$commissionQuery = "SELECT SUM(p.amount)*0.20 AS total_commissions 
                    FROM payments p
                    JOIN delivery_orders d ON p.order_id = d.order_id
                    WHERE p.payment_status = 'paid' AND d.status = 'delivered'";
$commissionResult = $conn->query($commissionQuery);
$totalCommissions = ($commissionResult && $row = $commissionResult->fetch_assoc()) ? $row['total_commissions'] : 0;

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Overview</title>
    <!-- Link to the dedicated admin dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .admin-dashboard {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            padding: 1rem;
            color: #fff;
        }

        .sidebar h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            margin-bottom: 1rem;
        }

        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 0.5rem;
        }

        .sidebar ul li a.active,
        .sidebar ul li a:hover {
            background: #34495e;
            border-radius: 4px;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
        }

        .main-content h1 {
            margin-bottom: 0.5rem;
        }

        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }

        .stats-cards .card {
            flex: 1;
            min-width: 200px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            text-align: center;
        }

        .stats-cards .card h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stats-cards .card p {
            font-size: 1.5rem;
            font-weight: bold;
            color: #27ae60;
            margin: 0;
        }

        .overview-description {
            margin-top: 2rem;
            max-width: 800px;
            line-height: 1.6;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="admin-dashboard">
        <!-- Left Side Navigation Bar -->
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="overview.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard Overview</a></li>
                <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
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
            <h1>Dashboard Overview</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! Here is a summary of system statistics.</p>
            <div class="stats-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?php echo $userCount; ?></p>
                </div>
                <div class="card">
                    <h3>Total Customers</h3>
                    <p><?php echo $customerCount; ?></p>
                </div>
                <div class="card">
                    <h3>Total Riders</h3>
                    <p><?php echo $riderCount; ?></p>
                </div>
                <div class="card">
                    <h3>Total Deliveries</h3>
                    <p><?php echo $deliveryCount; ?></p>
                </div>
                <div class="card">
                    <h3>Pending Deliveries</h3>
                    <p><?php echo $pendingCount; ?></p>
                </div>
                <div class="card">
                    <h3>Completed Deliveries</h3>
                    <p><?php echo $completedCount; ?></p>
                </div>
                <div class="card">
                    <h3>Total Payments</h3>
                    <p>Rs <?php echo number_format($totalPayments, 2); ?></p>
                </div>
                <div class="card">
                    <h3>Total Commissions Given</h3>
                    <p>Rs <?php echo number_format($totalCommissions, 2); ?></p>
                </div>
            </div>

            <!-- Overview Description -->
            <section class="overview-description">
                <h3>Dashboard Overview</h3>
                <p>This dashboard provides a comprehensive snapshot of system performance. Monitor user activity, track delivery statuses, review financial transactions, and manage complaints through the navigation panel. Stay informed and manage operations efficiently.</p>
            </section>
        </div>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>