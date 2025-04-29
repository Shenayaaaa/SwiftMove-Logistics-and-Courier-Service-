<?php
session_start();
include_once '../../includes/db.php';

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Access denied."]);
    exit;
}

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found."]);
    }
    $stmt->close();
} else {
    echo json_encode(["error" => "User ID is required."]);
}
