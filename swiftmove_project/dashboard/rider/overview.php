<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a rider
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'rider') {
    header("Location: ../../auth/login.php");
    exit;
}

$riderId = $_SESSION['user_id'];

// Calculate total commission earnings: 20% of all orders delivered and paid
$queryEarnings = "SELECT SUM(p.amount) AS total_paid 
                  FROM payments p 
                  JOIN delivery_orders d ON p.order_id = d.order_id 
                  WHERE d.rider_id = ? 
                    AND p.payment_status = 'paid' 
                    AND d.status = 'delivered'";
$stmt = $conn->prepare($queryEarnings);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();
$total_paid = 0;
if ($row = $result->fetch_assoc()) {
    $total_paid = $row['total_paid'];
}
$stmt->close();
$totalEarnings = 0.20 * $total_paid;

// Count pending orders for this rider
$queryPending = "SELECT COUNT(*) as pendingCount FROM delivery_orders WHERE rider_id = ? AND status = 'pending'";
$stmt = $conn->prepare($queryPending);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();
$pendingCount = 0;
if ($row = $result->fetch_assoc()) {
    $pendingCount = $row['pendingCount'];
}
$stmt->close();

// Count completed orders (delivered)
$queryCompleted = "SELECT COUNT(*) as completedCount FROM delivery_orders WHERE rider_id = ? AND status = 'delivered'";
$stmt = $conn->prepare($queryCompleted);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();
$completedCount = 0;
if ($row = $result->fetch_assoc()) {
    $completedCount = $row['completedCount'];
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rider Dashboard - Overview</title>
    <link rel="stylesheet" href="../../public/css/rider-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Overview Cards Styling */
        .cards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .card {
            flex: 1;
            min-width: 250px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 2rem;
            color: #007bff;
        }

        .card-info h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #343a40;
        }

        .card-info p {
            margin: 0.2rem 0 0;
            font-size: 1.5rem;
            color: #28a745;
            font-weight: bold;
        }

        /* Overview Description */
        .overview-description {
            max-width: 800px;
            margin: 2rem auto;
            font-size: 1rem;
            line-height: 1.5;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-motorcycle"></i> Rider Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php" class="active"><i class="fa-solid fa-chart-line"></i> Delivery Overview</a></li>
                <li><a href="assigned_orders.php"><i class="fa-solid fa-clipboard-list"></i> Assigned Orders</a></li>
                <li><a href="earnings.php"><i class="fa-solid fa-wallet"></i> Earnings</a></li>
                <li><a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> My Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
                <p>Your daily tasks and performance metrics at a glance.</p>
            </header>

            <!-- Card-Based Overview Section -->
            <section class="cards-container">
                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="card-info">
                        <h3>Total Earnings</h3>
                        <p>LKR <?php echo number_format($totalEarnings, 2); ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="card-info">
                        <h3>Pending Orders</h3>
                        <p><?php echo $pendingCount; ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div class="card-info">
                        <h3>Completed Orders</h3>
                        <p><?php echo $completedCount; ?></p>
                    </div>
                </div>
            </section>

            <!-- Overview Description -->
            <section class="overview-description">
                <h3>Dashboard Overview</h3>
                <p>This dashboard provides a quick snapshot of your performance as a rider. Monitor your commission earnings from delivered and paid orders, keep track of pending deliveries, and view the number of completed orders. Use the sidebar to navigate to your assigned orders, detailed earnings, notifications, and more.</p>
            </section>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>