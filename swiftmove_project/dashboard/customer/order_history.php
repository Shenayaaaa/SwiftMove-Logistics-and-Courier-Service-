<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../../auth/login.php");
    exit;
}

$customerId = $_SESSION['user_id'];

// Retrieve orders for this customer
$query = "SELECT order_id, pickup_location, dropoff_location, delivery_type, status, order_date 
          FROM delivery_orders 
          WHERE customer_id = ? 
          ORDER BY order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId);
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
    <title>Order Tracking - Customer Dashboard</title>
    <!-- Link to the common customer dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
    <!-- Optionally include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- QRCode.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        /* Additional styles for order tracking progress bars */
        .order-card {
            background: #fff;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .order-header h4 {
            margin: 0;
            color: #007bff;
        }

        .order-info p {
            margin: 0.5rem 0;
            font-size: 0.95rem;
            color: #555;
        }

        .progress-bar {
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
            height: 20px;
        }

        .progress-bar-inner {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            text-align: center;
            line-height: 20px;
            color: #fff;
            transition: width 0.5s ease;
        }

        .achievement {
            font-size: 0.9rem;
            font-weight: bold;
            color: #28a745;
        }

        /* QR Code Button */
        .qr-btn {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .qr-btn:hover {
            background-color: #0056b3;
        }

        /* QR Code Modal */
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
        }

        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            max-width: 300px;
            width: 90%;
            position: relative;
            text-align: center;
        }

        .modal-close {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 1.5rem;
            cursor: pointer;
            color: #aaa;
        }

        .modal-close:hover {
            color: #000;
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
                <li><a href="order_tracking.php" class="active"><i class="fa-solid fa-location-arrow"></i> Order Tracking</a></li>
                <li><a href="order_history.php"><i class="fa-solid fa-history"></i> Order History</a></li>
                <li><a href="payments.php"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Order Tracking</h2>
                <p>Review your current and past delivery orders with progress updates.</p>
            </header>

            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order):
                    // Determine progress percentage and achievement based on order status
                    $status = strtolower($order['status']);
                    switch ($status) {
                        case 'pending':
                            $progress = 20;
                            $achievement = "Order is pending confirmation.";
                            break;
                        case 'confirmed':
                            $progress = 40;
                            $achievement = "Order confirmed. Get ready!";
                            break;
                        case 'in_transit':
                            $progress = 70;
                            $achievement = "Your package is on the move!";
                            break;
                        case 'delivered':
                            $progress = 100;
                            $achievement = "Order delivered successfully!";
                            break;
                        case 'cancelled':
                            $progress = 0;
                            $achievement = "Order cancelled.";
                            break;
                        default:
                            $progress = 0;
                            $achievement = "";
                    }
                    // Prepare QR data string for this order
                    $qrData = "Order #{$order['order_id']}\nPickup: {$order['pickup_location']}\nDrop-off: {$order['dropoff_location']}\nType: {$order['delivery_type']}\nStatus: " . ucfirst($status);
                ?>
                    <div class="order-card">
                        <div class="order-header">
                            <h4>Order #<?php echo $order['order_id']; ?></h4>
                            <span><?php echo date("Y-m-d", strtotime($order['order_date'])); ?></span>
                        </div>
                        <div class="order-info">
                            <p><strong>Pickup:</strong> <?php echo htmlspecialchars($order['pickup_location']); ?></p>
                            <p><strong>Drop-off:</strong> <?php echo htmlspecialchars($order['dropoff_location']); ?></p>
                            <p><strong>Type:</strong> <?php echo $order['delivery_type']; ?></p>
                            <p><strong>Status:</strong> <?php echo ucfirst($status); ?></p>
                        </div>
                        <!-- Progress Bar -->
                        <div class="progress-bar">
                            <div class="progress-bar-inner" style="width: <?php echo $progress; ?>%;">
                                <?php echo $progress; ?>%
                            </div>
                        </div>
                        <?php if (!empty($achievement)): ?>
                            <p class="achievement"><?php echo $achievement; ?></p>
                        <?php endif; ?>
                        <!-- QR Code Button -->
                        <button class="qr-btn" data-qr="<?php echo htmlspecialchars($qrData); ?>">Show QR Code</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">No orders found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>

    <!-- QR Code Modal -->
    <div id="qrModal" class="modal">
        <div class="modal-content">
            <span id="qrModalClose" class="modal-close">&times;</span>
            <h3>Order QR Code</h3>
            <div id="qrcodeContainer"></div>
        </div>
    </div>

    <script>
        // When a QR Code button is clicked, generate and display the QR code in the modal
        document.querySelectorAll('.qr-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var qrData = btn.getAttribute('data-qr');
                var container = document.getElementById('qrcodeContainer');
                // Clear any existing QR code
                container.innerHTML = "";
                // Generate new QR code
                new QRCode(container, {
                    text: qrData,
                    width: 200,
                    height: 200
                });
                // Show the modal
                document.getElementById('qrModal').style.display = "flex";
            });
        });

        // Close modal when the close icon is clicked
        document.getElementById('qrModalClose').addEventListener('click', function() {
            document.getElementById('qrModal').style.display = "none";
        });

        // Optional: close modal when clicking outside the modal content
        window.addEventListener('click', function(e) {
            var modal = document.getElementById('qrModal');
            if (e.target == modal) {
                modal.style.display = "none";
            }
        });
    </script>
</body>

</html>