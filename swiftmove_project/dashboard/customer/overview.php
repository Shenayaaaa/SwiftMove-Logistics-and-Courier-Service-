    <?php
    session_start();
    include_once '../../includes/db.php';

    // Redirect if not logged in or not a customer
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
        header("Location: ../../auth/login.php");
        exit;
    }

    include_once '../../includes/header.php';

    // Example dynamic data retrieval (replace with real queries)
    $upcomingDeliveries = 3;
    $loyaltyPoints = 125;
    $recentOrders = 5;
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Customer Dashboard - Overview</title>
        <!-- Link to the redesigned customer dashboard CSS -->
        <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
        <!-- Optionally include FontAwesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    </head>

    <body>
        <div class="dashboard-container">
            <!-- Left Sidebar Navigation -->
            <aside class="sidebar">
                <h2><i class="fa-solid fa-house"></i> Dashboard</h2>
                <ul class="sidebar-nav">
                    <li><a href="overview.php" class="active"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                    <li><a href="new_delivery.php"><i class="fa-solid fa-plus-circle"></i> New Delivery</a></li>
                    <li><a href="order_tracking.php"><i class="fa-solid fa-location-arrow"></i> Order Tracking</a></li>
                    <li><a href="order_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Order History</a></li>
                    <li><a href="payments.php"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                    <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty</a></li>
                    <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                    <li><a href="complaints.php"><i class="fa-solid fa-exclamation-triangle"></i> My Complaints</a></li>
                    <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                <header class="dashboard-header">
                    <h2>Dashboard Overview</h2>
                    <p>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
                </header>

                <!-- Data Cards Section -->
                <div class="cards-container">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div class="card-info">
                            <h3>Upcoming Deliveries</h3>
                            <p><?php echo $upcomingDeliveries; ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon">
                            <i class="fa-solid fa-coins"></i>
                        </div>
                        <div class="card-info">
                            <h3>Loyalty Points</h3>
                            <p><?php echo $loyaltyPoints; ?></p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <div class="card-info">
                            <h3>Recent Orders</h3>
                            <p><?php echo $recentOrders; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Insights Section -->
                <div class="insights">
                    <h3>Order Trends</h3>
                    <p>Data visualization coming soon...</p>
                </div>
            </main>
        </div>
        <?php include_once '../../includes/footer.php'; ?>
    </body>

    </html>