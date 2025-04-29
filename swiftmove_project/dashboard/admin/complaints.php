<?php
session_start();
include_once '../../includes/db.php';

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$message = "";

// Process Update Complaint
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_complaint'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $complaint_type = trim($_POST['complaint_type']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    if (empty($complaint_type) || empty($description) || empty($status)) {
        $message = "Complaint type, description, and status are required.";
    } else {
        $stmt = $conn->prepare("UPDATE complaints SET complaint_type = ?, description = ?, status = ? WHERE complaint_id = ?");
        $stmt->bind_param("sssi", $complaint_type, $description, $status, $complaint_id);
        if ($stmt->execute()) {
            $message = "Complaint updated successfully.";
        } else {
            $message = "Error updating complaint: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Process Delete Complaint
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_complaint'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $stmt = $conn->prepare("DELETE FROM complaints WHERE complaint_id = ?");
    $stmt->bind_param("i", $complaint_id);
    if ($stmt->execute()) {
        $message = "Complaint deleted successfully.";
    } else {
        $message = "Error deleting complaint: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve all complaints
$query = "SELECT complaint_id, user_id, order_id, complaint_type, description, status, created_at, updated_at 
          FROM complaints
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
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
    <title>Manage Complaints - Admin Dashboard</title>
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

        /* Action buttons */
        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: background 0.3s ease;
        }

        .update-btn {
            background-color: #007bff;
            color: #fff;
        }

        .update-btn:hover {
            background-color: #0056b3;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .view-btn {
            background-color: #6c757d;
            color: #fff;
        }

        .view-btn:hover {
            background-color: #5a6268;
        }

        /* Inline update form styling */
        .inline-form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .inline-form select,
        .inline-form textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .inline-form textarea {
            resize: vertical;
        }

        .inline-form button {
            align-self: flex-start;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .inline-form .btn-update {
            background-color: #28a745;
            color: #fff;
        }

        .inline-form .btn-update:hover {
            background-color: #218838;
        }

        /* Modal styling for viewing user details */
        .modal {
            display: none;
            position: fixed;
            z-index: 200;
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
            width: 90%;
            max-width: 400px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .modal-content h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            text-align: center;
        }

        .modal-content p {
            margin: 0.5rem 0;
        }

        .modal .close {
            position: absolute;
            top: 0.5rem;
            right: 0.75rem;
            font-size: 1.5rem;
            color: #aaa;
            cursor: pointer;
        }

        .modal .close:hover {
            color: #000;
        }
    </style>
    <script>
        // Function to view user details using AJAX
        function viewUser(userId) {
            fetch(`view_user.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    let modalContent = document.getElementById("viewUserContent");
                    if (data.error) {
                        modalContent.innerHTML = `<p>${data.error}</p>`;
                    } else {
                        modalContent.innerHTML = `
                        <h3>User Details</h3>
                        <p><strong>Full Name:</strong> ${data.full_name}</p>
                        <p><strong>Email:</strong> ${data.email}</p>
                        <p><strong>Phone:</strong> ${data.phone}</p>
                    `;
                    }
                    document.getElementById("viewUserModal").style.display = "flex";
                })
                .catch(error => {
                    console.error("Error fetching user details:", error);
                });
        }

        function closeUserModal() {
            document.getElementById("viewUserModal").style.display = "none";
        }
        window.onclick = function(event) {
            let modal = document.getElementById("viewUserModal");
            if (event.target == modal) {
                closeUserModal();
            }
        }
    </script>
</head>

<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2><i class="fa-solid fa-user-shield"></i> Admin Panel</h2>
            <ul class="sidebar-nav">
                <li><a href="overview.php"><i class="fa-solid fa-chart-line"></i> Dashboard Overview</a></li>
                <li><a href="manage_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
                <li><a href="manage_deliveries.php"><i class="fa-solid fa-truck"></i> Manage Deliveries</a></li>
                <li><a href="payment_management.php"><i class="fa-solid fa-wallet"></i> Payment Management</a></li>
                <li><a href="loyalty_rewards.php"><i class="fa-solid fa-gift"></i> Loyalty & Rewards</a></li>
                <li><a href="system_settings.php"><i class="fa-solid fa-cogs"></i> System Settings</a></li>
                <li><a href="complaints.php" class="active"><i class="fa-solid fa-exclamation-triangle"></i> Manage Complaints</a></li>
                <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="dashboard-header">
                <h2>Manage Complaints</h2>
                <p>Review, update, and delete customer and rider complaints.</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (count($complaints) > 0): ?>
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Order ID</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?php echo $complaint['complaint_id']; ?></td>
                                <td><?php echo $complaint['user_id']; ?></td>
                                <td><?php echo $complaint['order_id'] ? $complaint['order_id'] : 'N/A'; ?></td>
                                <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['description']); ?></td>
                                <td><?php echo ucfirst($complaint['status']); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($complaint['created_at'])); ?></td>
                                <td>
                                    <!-- Inline Update Form -->
                                    <form method="post" action="complaints.php" class="inline-form">
                                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                        <select name="complaint_type" required>
                                            <option value="customer" <?php echo ($complaint['complaint_type'] == 'customer') ? "selected" : ""; ?>>Customer</option>
                                            <option value="rider" <?php echo ($complaint['complaint_type'] == 'rider') ? "selected" : ""; ?>>Rider</option>
                                        </select>
                                        <textarea name="description" required><?php echo htmlspecialchars($complaint['description']); ?></textarea>
                                        <select name="status" required>
                                            <option value="open" <?php echo ($complaint['status'] == 'open') ? "selected" : ""; ?>>Open</option>
                                            <option value="in_progress" <?php echo ($complaint['status'] == 'in_progress') ? "selected" : ""; ?>>In Progress</option>
                                            <option value="resolved" <?php echo ($complaint['status'] == 'resolved') ? "selected" : ""; ?>>Resolved</option>
                                        </select>
                                        <div style="margin-top:5px;">
                                            <button type="submit" name="update_complaint" class="btn-update"><i class="fa-solid fa-save"></i> Update</button>
                                        </div>
                                    </form>
                                    <!-- Delete Complaint Form -->
                                    <form method="post" action="complaints.php" onsubmit="return confirm('Are you sure you want to delete this complaint?');" style="margin-top:5px;">
                                        <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                        <button type="submit" name="delete_complaint" class="action-btn delete-btn"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                    <!-- Button to View User Details -->
                                    <!-- <button class="action-btn view-btn" onclick="viewUser(<?php echo $complaint['user_id']; ?>)"> -->
                                        <!-- <i class="fa-solid fa-eye"></i> View User -->
                                    <!-- </button> -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align:center;">No complaints found.</p>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal for Viewing User Details -->
            <!-- <div id="viewUserModal" class="modal"> -->
        <!-- <div class="modal-content"> -->
            <!-- <span class="close" onclick="closeUserModal()">&times;</span> -->
            <!-- <div id="viewUserContent"> -->
                <!-- User details will be loaded here via AJAX -->
            <!-- </div> -->
        <!-- </div> -->
    <!-- </div> -->

    <?php include_once '../../includes/footer.php'; ?>
</body>

</html>