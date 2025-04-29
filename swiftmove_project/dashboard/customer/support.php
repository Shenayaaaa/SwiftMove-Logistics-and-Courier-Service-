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

// Process new support ticket submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ticket'])) {
    $subject = trim($_POST['subject']);
    $messageContent = trim($_POST['message']);

    if (empty($subject) || empty($messageContent)) {
        $message = "Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, message, status) VALUES (?, ?, ?, 'open')");
        $stmt->bind_param("iss", $customerId, $subject, $messageContent);
        if ($stmt->execute()) {
            $message = "Your support ticket has been submitted successfully.";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve all support tickets for this customer
$query = "SELECT ticket_id, subject, message, status, created_at FROM support_tickets WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId);
$stmt->execute();
$result = $stmt->get_result();

$tickets = [];
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support - Customer Dashboard</title>
    <!-- Link to the common customer dashboard CSS -->
    <link rel="stylesheet" href="../../public/css/customer-dashboard.css">
    <!-- Optionally include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Additional styling for the support page */
        .support-form {
            max-width: 600px;
            margin: 1.5rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .support-form h3 {
            margin-bottom: 1rem;
            color: #007bff;
        }

        .support-form .form-group {
            margin-bottom: 1rem;
        }

        .support-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .support-form input[type="text"],
        .support-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .support-form textarea {
            resize: vertical;
            min-height: 100px;
        }

        .support-form button {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .support-form button:hover {
            background-color: #218838;
        }

        .message {
            text-align: center;
            padding: 0.75rem;
            margin: 1rem auto;
            max-width: 600px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            color: #155724;
        }

        /* Table styles for support ticket history */
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .tickets-table th,
        .tickets-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .tickets-table th {
            background-color: #f1f8ff;
        }

        .ticket-status-open {
            color: #ffc107;
            font-weight: bold;
        }

        .ticket-status-closed {
            color: #28a745;
            font-weight: bold;
        }

        .ticket-status-in_progress {
            color: #17a2b8;
            font-weight: bold;
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
                <li><a href="loyalty.php"><i class="fa-solid fa-gift"></i> Loyalty & Rewards</a></li>
                <li><a href="support.php" class="active"><i class="fa-solid fa-headset"></i> Support</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Support</h2>
                <p>Submit your queries and review your support ticket history.</p>
            </header>

            <!-- Support Ticket Submission Form -->
            <div class="support-form">
                <h3>Submit a New Ticket</h3>
                <?php if (!empty($message)): ?>
                    <div class="message"><?php echo $message; ?></div>
                <?php endif; ?>
                <form id="supportForm" method="post" action="support.php">
                    <div class="form-group">
                        <label for="subject">Subject:</label>
                        <input type="text" id="subject" name="subject" placeholder="Enter ticket subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message:</label>
                        <textarea id="message" name="message" placeholder="Describe your issue" required></textarea>
                    </div>
                    <button type="submit" name="submit_ticket"><i class="fa-solid fa-paper-plane"></i> Submit Ticket</button>
                </form>
            </div>

            <!-- Support Ticket History -->
            <h3 style="margin-top:2rem; text-align:center; color:#007bff;">Your Support Tickets</h3>
            <?php if (count($tickets) > 0): ?>
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><?php echo $ticket['ticket_id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td class="ticket-status-<?php echo strtolower(str_replace(" ", "_", $ticket['status'])); ?>">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </td>
                                <td><?php echo date("Y-m-d", strtotime($ticket['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">You have not submitted any support tickets yet.</p>
            <?php endif; ?>

        </main>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>