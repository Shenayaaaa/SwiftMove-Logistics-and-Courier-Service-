<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: ../../auth/login.php");
    exit;
}

$customerId = $_SESSION['user_id'];
$message = "";

// Process Create Payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_payment'])) {
    $order_id = intval($_POST['order_id']);
    $payment_method = trim($_POST['payment_method']);
    $amount = floatval($_POST['amount']);
    $transaction_details = trim($_POST['transaction_details']);

    // Payment status will be set as 'pending' by default
    $stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, amount, transaction_details) VALUES (?, ?, 'pending', ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isds", $order_id, $payment_method, $amount, $transaction_details);
        if ($stmt->execute()) {
            $message = "Payment created successfully. Please proceed to complete your payment.";
        } else {
            $message = "Error creating payment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "Preparation error: " . $conn->error;
    }
}

// Retrieve eligible orders for payment creation
// We only want orders that belong to the customer and that do NOT already have a successful ('paid') payment.
$queryOrders = "SELECT order_id FROM delivery_orders 
                WHERE customer_id = ? 
                AND order_id NOT IN (SELECT order_id FROM payments WHERE payment_status = 'paid')";
$stmt = $conn->prepare($queryOrders);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$eligibleOrders = [];
while ($row = $result->fetch_assoc()) {
    // For demonstration, we simulate a dummy amount as order_id * 100.
    $row['amount'] = $row['order_id'] * 100;
    $eligibleOrders[] = $row;
}
$stmt->close();

// Retrieve payment history (only paid payments) for the logged-in customer
$queryHistory = "SELECT p.payment_id, p.payment_method, p.payment_status, p.amount, p.payment_date, p.transaction_details 
                 FROM payments p
                 JOIN delivery_orders d ON p.order_id = d.order_id
                 WHERE d.customer_id = ? AND p.payment_status = 'paid'
                 ORDER BY p.payment_date DESC";
$stmt = $conn->prepare($queryHistory);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payments - Customer Dashboard</title>
    <!-- Link to the common customer dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* General message styling */
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

        /* Payment creation form styling */
        .payment-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        .payment-form h3 {
            margin-bottom: 1rem;
            color: #007bff;
        }

        .payment-form .form-group {
            margin-bottom: 1rem;
        }

        .payment-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .payment-form input[type="number"],
        .payment-form select,
        .payment-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .payment-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .payment-form button {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .payment-form button:hover {
            background-color: #218838;
        }

        /* Payments table styling */
        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }

        .payments-table th,
        .payments-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .payments-table th {
            background-color: #f1f8ff;
        }

        .payment-status-paid {
            color: green;
            font-weight: bold;
        }

        .payment-status-pending {
            color: orange;
            font-weight: bold;
        }

        .payment-status-failed {
            color: red;
            font-weight: bold;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const orderSelect = document.getElementById("order_id");
            const amountInput = document.getElementById("amount");

            orderSelect.addEventListener("change", function() {
                const selectedOption = orderSelect.options[orderSelect.selectedIndex];
                const orderAmount = selectedOption.getAttribute("data-amount");
                if (orderAmount) {
                    amountInput.value = orderAmount;
                    amountInput.readOnly = true;
                } else {
                    amountInput.value = "";
                    amountInput.readOnly = false;
                }
            });
        });
    </script>
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
                <li><a href="payments.php" class="active"><i class="fa-solid fa-file-invoice-dollar"></i> Payments</a></li>
                <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty</a></li>
                <li><a href="support.php"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Make a Payment</h2>
                <p>Create a new payment for an order that is pending or has a failed payment.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Payment Creation Form -->
            <div class="payment-form">
                <h3>Create Payment</h3>
                <form id="paymentForm" method="post" action="payments.php">
                    <div class="form-group">
                        <label for="order_id">Order ID:</label>
                        <select id="order_id" name="order_id" required>
                            <option value="">Select an order</option>
                            <?php foreach ($eligibleOrders as $order): ?>
                                <option value="<?php echo $order['order_id']; ?>" data-amount="<?php echo $order['amount']; ?>">
                                    <?php echo $order['order_id']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method:</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="CreditCard">Credit Card</option>
                            <option value="BankTransfer">Bank Transfer</option>
                            <option value="PayPal">PayPal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount (LKR):</label>
                        <input type="number" id="amount" name="amount" step="0.01" placeholder="Amount will be auto-filled" required>
                    </div>
                    <div class="form-group">
                        <label for="transaction_details">Transaction Details:</label>
                        <textarea id="transaction_details" name="transaction_details" placeholder="Enter transaction details (optional)"></textarea>
                    </div>
                    <button type="submit" name="create_payment"><i class="fa-solid fa-plus"></i> Create Payment</button>
                </form>
            </div>

            <header class="dashboard-header" style="margin-top:2rem;">
                <h2>Payment History</h2>
                <p>Review all your paid transactions in LKR.</p>
            </header>

            <!-- Payment History Table (showing only 'paid' payments) -->
            <?php if (count($payments) > 0): ?>
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Amount (LKR)</th>
                            <th>Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo $payment['payment_id']; ?></td>
                                <td><?php echo ucfirst($payment['payment_method']); ?></td>
                                <td class="payment-status-<?php echo strtolower($payment['payment_status']); ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </td>
                                <td><?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['transaction_details']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No payment records found.</p>
            <?php endif; ?>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>