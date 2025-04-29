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

// Process Mark as Read
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_read'])) {
    $notification_id = intval($_POST['notification_id']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $riderId);
    if ($stmt->execute()) {
        $message = "Notification marked as read.";
    } else {
        $message = "Error updating notification: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve notifications for this rider
$query = "SELECT notification_id, title, message, is_read, created_at 
          FROM notifications 
          WHERE user_id = ? 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $riderId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications - Rider Dashboard</title>
    <!-- Link to the rider dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/rider-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for the notifications page */
        .notifications-container {
            max-width: 1000px;
            margin: 2rem auto;
        }

        .notification-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        .notification-icon {
            font-size: 1.5rem;
            color: #007bff;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #343a40;
        }

        .notification-message {
            font-size: 0.95rem;
            color: #555;
        }

        .notification-date {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }

        /* Unread notifications highlighted */
        .notification-unread {
            border-left: 4px solid #007bff;
            padding-left: 0.75rem;
        }

        /* Mark as Read button styling */
        .mark-read-btn {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            transition: background 0.3s ease;
        }

        .mark-read-btn:hover {
            background-color: #218838;
        }

        /* Message styling */
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
    <script>
        // Optionally, you can add JavaScript to remove the notification from the DOM once marked as read.
    </script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Left Sidebar Navigation -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-motorcycle"></i> Rider Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                <li><a href="assigned_orders.php"><i class="fa-solid fa-clipboard-list"></i> Assigned Orders</a></li>
                <li><a href="earnings.php"><i class="fa-solid fa-wallet"></i> Earnings</a></li>
                <li><a href="notifications.php" class="active"><i class="fa-solid fa-bell"></i> Notifications</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Notifications</h2>
                <p>Stay updated with the latest alerts and messages.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="notifications-container">
                <?php if (count($notifications) > 0): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-card <?php echo $notification['is_read'] ? "" : "notification-unread"; ?>">
                            <div class="notification-icon">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">
                                    <?php echo htmlspecialchars($notification['title']); ?>
                                </div>
                                <div class="notification-message">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </div>
                                <div class="notification-date">
                                    <?php echo date("Y-m-d H:i", strtotime($notification['created_at'])); ?>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <form method="post" action="notifications.php" style="margin-top: 0.5rem;">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                        <button type="submit" name="mark_read" class="mark-read-btn">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;">No notifications found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>