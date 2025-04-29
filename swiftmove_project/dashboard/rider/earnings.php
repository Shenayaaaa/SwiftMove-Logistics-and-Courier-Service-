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

// Retrieve payout records for this rider
$query = "SELECT payout_id, amount, request_date, payout_date, status 
          FROM earnings_payout 
          WHERE rider_id = ? 
          ORDER BY request_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();

$payouts = [];
while ($row = $result->fetch_assoc()) {
    $payouts[] = $row;
}
$stmt->close();

// Calculate total commission earnings from completed & paid orders
// Here we join payments and delivery_orders. We assume that an order is 'delivered' when completed.
$query = "SELECT SUM(p.amount) AS total_paid 
          FROM payments p
          JOIN delivery_orders d ON p.order_id = d.order_id
          WHERE d.rider_id = ? 
            AND p.payment_status = 'paid'
            AND d.status = 'delivered'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();
$totalPaid = 0;
if ($row = $result->fetch_assoc()) {
    $totalPaid = $row['total_paid'];
}
$stmt->close();

// Rider commission is 20% of total paid amount
$totalEarnings = 0.20 * $totalPaid;

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Earnings - Rider Dashboard</title>
    <!-- Link to the rider dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/rider-dashboard.css">
    <!-- Optionally include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Styling for earnings summary card */
        .summary-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .summary-card h3 {
            color: #007bff;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .summary-card p {
            font-size: 1.75rem;
            font-weight: bold;
            color: #28a745;
        }

        /* Earnings table styling */
        .earnings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .earnings-table th,
        .earnings-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .earnings-table th {
            background-color: #f1f8ff;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .status-approved {
            color: #28a745;
            font-weight: bold;
        }

        .status-rejected {
            color: #e74c3c;
            font-weight: bold;
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
                <li><a href="assigned_orders.php"><i class="fa-solid fa-clipboard-list"></i> Assigned Orders</a></li>
                <li><a href="earnings.php" class="active"><i class="fa-solid fa-wallet"></i> Earnings</a></li>
                <li><a href="notifications.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Earnings & Payout</h2>
                <p>Review your total commission earnings and payout requests.</p>
            </header>

            <!-- Earnings Summary Card -->
            <div class="summary-card">
                <h3>Total Commission Earnings</h3>
                <p>LKR <?php echo number_format($totalEarnings, 2); ?></p>
            </div>

            <!-- Payout Records Table -->
            <?php if (count($payouts) > 0): ?>
                <table class="earnings-table">
                    <thead>
                        <tr>
                            <th>Payout ID</th>
                            <th>Amount (LKR)</th>
                            <th>Request Date</th>
                            <th>Payout Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $payout): ?>
                            <tr>
                                <td><?php echo $payout['payout_id']; ?></td>
                                <td><?php echo number_format($payout['amount'], 2); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($payout['request_date'])); ?></td>
                                <td><?php echo $payout['payout_date'] ? date("Y-m-d", strtotime($payout['payout_date'])) : 'N/A'; ?></td>
                                <td class="status-<?php echo strtolower($payout['status']); ?>">
                                    <?php echo ucfirst($payout['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No payout records found.</p>
            <?php endif; ?>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>