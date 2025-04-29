<?php
session_start();
include_once '../../includes/db.php';

// Only allow logged in users with role customer or rider
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['customer', 'rider'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$message = "";

// Process Create Complaint
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_complaint'])) {
    $user_id = $_SESSION['user_id'];
    $order_id = !empty($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $complaint_type = trim($_POST['complaint_type']);
    $description = trim($_POST['description']);

    if (empty($complaint_type) || empty($description)) {
        $message = "Complaint type and description are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, order_id, complaint_type, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $order_id, $complaint_type, $description);
        if ($stmt->execute()) {
            $message = "Complaint created successfully.";
        } else {
            $message = "Error creating complaint: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Retrieve complaints for current user
$stmt = $conn->prepare("SELECT complaint_id, order_id, complaint_type, description, status, created_at FROM complaints WHERE user_id = ? ORDER BY created_at DESC");
$user_id = $_SESSION['user_id'];
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$complaints = [];
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}
$stmt->close();

include_once '../../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Complaints</title>
    <link rel="stylesheet" href="../../public/css/admin-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
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

        /* Complaint creation form styling */
        .complaint-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        .complaint-form h3 {
            margin-bottom: 1rem;
            color: #007bff;
        }

        .complaint-form .form-group {
            margin-bottom: 1rem;
        }

        .complaint-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .complaint-form input[type="number"],
        .complaint-form select,
        .complaint-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .complaint-form textarea {
            resize: vertical;
            min-height: 80px;
        }

        .complaint-form button {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 1rem;
        }

        .complaint-form button:hover {
            background-color: #218838;
        }

        /* Complaints table styling */
        .complaints-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .complaints-table th,
        .complaints-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .complaints-table th {
            background-color: #f1f8ff;
        }
    </style>
</head>

<body>
    <div class="admin-dashboard">
        <!-- Sidebar for Customers/Riders -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-user"></i> Dashboard</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Overview</a></li>
                <li><a href="complaints.php" class="active"><i class="fa-solid fa-exclamation-triangle"></i> My Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>
        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>My Complaints</h2>
                <p>Create a new complaint and review your previous submissions.</p>
            </header>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Complaint Creation Form -->
            <div class="complaint-form">
                <h3>Create New Complaint</h3>
                <form method="post" action="complaints.php">
                    <div class="form-group">
                        <label for="order_id">Order ID (optional):</label>
                        <input type="number" id="order_id" name="order_id" placeholder="Enter order ID if applicable">
                    </div>
                    <div class="form-group">
                        <label for="complaint_type">Complaint Type:</label>
                        <select id="complaint_type" name="complaint_type" required>
                            <option value="">Select Complaint Type</option>
                            <!-- Pre-select based on user's role: Customers generally complain about riders, riders complain about customers -->
                            <option value="customer" <?php if ($_SESSION['role'] == 'rider') echo 'selected'; ?>>Customer</option>
                            <option value="rider" <?php if ($_SESSION['role'] == 'customer') echo 'selected'; ?>>Rider</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea id="description" name="description" placeholder="Enter complaint details" required></textarea>
                    </div>
                    <button type="submit" name="create_complaint"><i class="fa-solid fa-plus"></i> Submit Complaint</button>
                </form>
            </div>

            <!-- Display User's Complaints -->
            <?php if (count($complaints) > 0): ?>
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?php echo $complaint['complaint_id']; ?></td>
                                <td><?php echo $complaint['order_id'] ? $complaint['order_id'] : 'N/A'; ?></td>
                                <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['description']); ?></td>
                                <td><?php echo ucfirst($complaint['status']); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($complaint['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">You have not submitted any complaints yet.</p>
            <?php endif; ?>
        </main>
    </div>
    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>