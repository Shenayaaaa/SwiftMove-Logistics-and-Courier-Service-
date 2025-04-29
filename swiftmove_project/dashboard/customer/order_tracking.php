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
    <!-- Link to the dedicated customer dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
    <!-- Optionally include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for the orders table */
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

        .view-map-btn {
            padding: 0.4rem 0.8rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .view-map-btn:hover {
            background-color: #0056b3;
        }

        /* Map Modal styling */
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
            max-width: 600px;
            width: 90%;
            position: relative;
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

        /* Responsive iframe for map */
        .map-iframe {
            width: 100%;
            height: 400px;
            border: 0;
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
                <li><a href="order_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Order History</a></li>
                <li><a href="payments.php"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Order Tracking</h2>
                <p>Review your current and past delivery orders. Click "View Map" for real-time tracking details.</p>
            </header>

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
                                <td><?php echo ucfirst($order['status']); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($order['order_date'])); ?></td>
                                <td>
                                    <button class="view-map-btn"
                                        onclick="viewMap('<?php echo addslashes(htmlspecialchars($order['pickup_location'])); ?>',
                                                          '<?php echo addslashes(htmlspecialchars($order['dropoff_location'])); ?>')">
                                        <i class="fa-solid fa-map"></i> View Map
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No orders found.</p>
            <?php endif; ?>
        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>

    <!-- Map Modal -->
    <div id="mapModal" class="modal">
        <div class="modal-content">
            <span id="mapModalClose" class="modal-close">&times;</span>
            <h3>Order Tracking Map</h3>
            <iframe id="mapFrame" class="map-iframe" src="" allowfullscreen></iframe>
        </div>
    </div>

    <script>
        // Open the modal and set the iframe source using pickup and drop-off addresses
        function viewMap(pickup, dropoff) {
            var baseUrl = "https://www.google.com/maps/embed/v1/directions?key=AIzaSyAQXDZ_wdWis0b3gF6286tXhKhE-OKABAo";
            var url = baseUrl + "&origin=" + encodeURIComponent(pickup) + "&destination=" + encodeURIComponent(dropoff);
            document.getElementById("mapFrame").src = url;
            document.getElementById("mapModal").style.display = "flex";
        }

        // Close the modal
        document.getElementById("mapModalClose").addEventListener("click", function() {
            document.getElementById("mapModal").style.display = "none";
            document.getElementById("mapFrame").src = "";
        });

        // Close the modal when clicking outside of the modal content
        window.addEventListener("click", function(event) {
            var modal = document.getElementById("mapModal");
            if (event.target == modal) {
                modal.style.display = "none";
                document.getElementById("mapFrame").src = "";
            }
        });
    </script>
</body>

</html>