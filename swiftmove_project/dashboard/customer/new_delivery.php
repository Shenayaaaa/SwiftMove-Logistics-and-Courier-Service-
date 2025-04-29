<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../../auth/login.php");
    exit;
}

$error = "";

// Process POST submission for placing an order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input values
    $pickup_location = trim($_POST['pickup_location']);
    $dropoff_location = trim($_POST['dropoff_location']);
    $delivery_type = $_POST['delivery_type'];
    $package_size = trim($_POST['package_size']);
    $weight = floatval($_POST['weight']);
    $estimated_price = floatval($_POST['estimated_price']);

    // Basic validation
    if (empty($pickup_location) || empty($dropoff_location) || empty($package_size) || $weight <= 0) {
        $error = "Please fill in all required fields with valid data.";
    } else {
        // Insert the new delivery order into the database (status set as 'pending')
        $stmt = $conn->prepare("INSERT INTO delivery_orders (customer_id, pickup_location, dropoff_location, delivery_type, package_size, weight, price, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $customer_id = $_SESSION['user_id'];
        $stmt->bind_param("isssssd", $customer_id, $pickup_location, $dropoff_location, $delivery_type, $package_size, $weight, $estimated_price);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Delivery order placed successfully!');
                    window.location.href = 'order_tracking.php';
                  </script>";
            exit;
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch data for dynamic dropdowns
$locations = [];
$deliveryTypes = [];
$packageSizes = [];

// Fetch locations
$sql = "SELECT id, location_name, distance FROM locations";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
}

// Fetch delivery types
$sql = "SELECT id, type, cost FROM delivery_types";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deliveryTypes[] = $row;
    }
}

// Fetch package sizes
$sql = "SELECT id, size, cost, note FROM package_sizes";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $packageSizes[] = $row;
    }
}

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Delivery - Customer Dashboard</title>
    <link rel="stylesheet" href="../../public/css/new_delivery.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script defer>
        // Set a fixed cost per km rate (adjust as necessary)
        const RATE_PER_KM = 5;

        // When the document is ready, add event listeners
        document.addEventListener('DOMContentLoaded', function() {
            const calculateBtn = document.getElementById('calculatePrice');

            calculateBtn.addEventListener('click', function() {
                // Get selected pickup and dropoff options and their distances (using data attributes)
                const pickupSelect = document.getElementById('pickup_location');
                const dropoffSelect = document.getElementById('dropoff_location');
                const pickupDistance = parseFloat(pickupSelect.options[pickupSelect.selectedIndex].getAttribute('data-distance'));
                const dropoffDistance = parseFloat(dropoffSelect.options[dropoffSelect.selectedIndex].getAttribute('data-distance'));

                // Calculate absolute distance difference
                const distanceKm = Math.abs(pickupDistance - dropoffDistance);

                // Get the cost for the selected delivery type
                const deliveryTypeSelect = document.getElementById('delivery_type');
                const deliveryTypeCost = parseFloat(deliveryTypeSelect.options[deliveryTypeSelect.selectedIndex].getAttribute('data-cost'));

                // Get the cost for the selected package size
                const packageSizeSelect = document.getElementById('package_size');
                const packageSizeCost = parseFloat(packageSizeSelect.options[packageSizeSelect.selectedIndex].getAttribute('data-cost'));

                // Calculate total price
                const totalPrice = (distanceKm * RATE_PER_KM) + deliveryTypeCost + packageSizeCost;

                // Display the calculated price (rounded to 2 decimals)
                document.getElementById('estimated_price').value = totalPrice.toFixed(2);
            });
        });
    </script>
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2><i class="fa-solid fa-truck"></i> Delivery</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                <li><a href="new_delivery.php" class="active"><i class="fa-solid fa-plus-circle"></i> New Delivery</a></li>
                <li><a href="order_tracking.php"><i class="fa-solid fa-location-arrow"></i> Order Tracking</a></li>
                <li><a href="order_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Order History</a></li>
                <li><a href="payments.php"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h2>New Delivery</h2>
                <p>Enter your delivery details to get a pricing estimate and place your order.</p>
            </header>

            <?php if (!empty($error)) : ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="deliveryForm" method="post" action="new_delivery.php">
                <!-- Pickup Location Dropdown -->
                <div class="form-group">
                    <label for="pickup_location">Pickup Location:</label>
                    <select id="pickup_location" name="pickup_location" required>
                        <option value="">Select Pickup Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location['location_name']); ?>" data-distance="<?php echo htmlspecialchars($location['distance']); ?>">
                                <?php echo htmlspecialchars($location['location_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Drop-off Location Dropdown -->
                <div class="form-group">
                    <label for="dropoff_location">Drop-off Location:</label>
                    <select id="dropoff_location" name="dropoff_location" required>
                        <option value="">Select Drop-off Location</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo htmlspecialchars($location['location_name']); ?>" data-distance="<?php echo htmlspecialchars($location['distance']); ?>">
                                <?php echo htmlspecialchars($location['location_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Delivery Type Dropdown -->
                <div class="form-group">
                    <label for="delivery_type">Delivery Type:</label>
                    <select id="delivery_type" name="delivery_type" required>
                        <option value="">Select Delivery Type</option>
                        <?php foreach ($deliveryTypes as $dType): ?>
                            <option value="<?php echo htmlspecialchars($dType['type']); ?>" data-cost="<?php echo htmlspecialchars($dType['cost']); ?>">
                                <?php echo htmlspecialchars($dType['type']); ?> Delivery (Additional LKR <?php echo htmlspecialchars($dType['cost']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Package Size Dropdown -->
                <div class="form-group">
                    <label for="package_size">Package Size:</label>
                    <select id="package_size" name="package_size" required>
                        <option value="">Select Package Size</option>
                        <?php foreach ($packageSizes as $pSize): ?>
                            <option value="<?php echo htmlspecialchars($pSize['size']); ?>" data-cost="<?php echo htmlspecialchars($pSize['cost']); ?>">
                                <?php echo htmlspecialchars($pSize['size']); ?> (Fixed LKR <?php echo htmlspecialchars($pSize['cost']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>
                        <?php
                        // Display note from the first record (or adjust as necessary)
                        if (!empty($packageSizes)) {
                            echo htmlspecialchars($packageSizes[0]['note']);
                        }
                        ?>
                    </small>
                </div>

                <!-- Weight Input -->
                <div class="form-group">
                    <label for="weight">Weight (kg):</label>
                    <input type="number" id="weight" name="weight" placeholder="Enter weight" step="0.1" required>
                </div>

                <!-- Estimated Price (Calculated) -->
                <div class="form-group">
                    <label for="estimated_price">Estimated Price (LKR):</label>
                    <input type="number" id="estimated_price" name="estimated_price" placeholder="Price will be calculated" step="0.01" readonly>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" id="calculatePrice"><i class="fa-solid fa-calculator"></i> Calculate Price</button>
                    <button type="submit"><i class="fa-solid fa-paper-plane"></i> Place Order</button>
                </div>
            </form>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>