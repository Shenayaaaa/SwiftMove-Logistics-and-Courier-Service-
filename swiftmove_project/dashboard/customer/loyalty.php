<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../../auth/login.php");
    exit;
}

$customerId = $_SESSION['user_id'];

// Retrieve loyalty rewards data for the customer
$query = "SELECT current_tier, points FROM loyalty_rewards WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();
$loyaltyData = $result->fetch_assoc();
$stmt->close();

// If no record found, set defaults
if (!$loyaltyData) {
    $loyaltyData = [
        'current_tier' => 'Bronze',
        'points'       => 0
    ];
}

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loyalty & Rewards - Customer Dashboard</title>
    <!-- Link to the common customer dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
    <!-- Optionally include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for the loyalty section */
        .loyalty-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .loyalty-container h2 {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 1rem;
        }

        .loyalty-info {
            font-size: 1.2rem;
            color: #343a40;
            margin-bottom: 1rem;
        }

        .loyalty-tier {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .loyalty-points {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 1.5rem;
        }

        .reward-tips {
            font-size: 1rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-house"></i> Dashboard</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                <li><a href="new_delivery.php"><i class="fa-solid fa-plus-circle"></i> New Delivery</a></li>
                <li><a href="order_tracking.php"><i class="fa-solid fa-location-arrow"></i> Order Tracking</a></li>
                <li><a href="order_history.php"><i class="fa-solid fa-history"></i> Order History</a></li>
                <li><a href="payments.php"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="loyalty.php" class="active"><i class="fa-solid fa-gift"></i> Loyalty & Rewards</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Loyalty & Rewards</h2>
                <p>Track your rewards, view your membership tier, and see available offers.</p>
            </header>

            <div class="loyalty-container">
                <div class="loyalty-tier">
                    Current Tier: <?php echo htmlspecialchars($loyaltyData['current_tier']); ?>
                </div>
                <div class="loyalty-points">
                    <?php echo number_format($loyaltyData['points']); ?> Points
                </div>
                <div class="loyalty-info">
                    As you accumulate more points, you'll unlock higher tiers and exclusive discounts!
                </div>
                <div class="reward-tips">
                    Tip: Refer friends or complete additional deliveries to earn bonus points!
                </div>
            </div>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>