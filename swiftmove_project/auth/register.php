<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '../includes/db.php';
include_once '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $name              = trim($_POST['name']);
    $email             = trim($_POST['email']);
    $phone             = trim($_POST['phone']);
    $password          = trim($_POST['password']);
    $confirm_password  = trim($_POST['confirm_password']);
    $role              = $_POST['role'];  // 'customer' or 'rider'

    // Basic server-side validations
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if (empty($phone) || !preg_match('/^[0-9]{7,15}$/', $phone)) $errors[] = "A valid phone number is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // Check if email or phone already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email or phone already exists.";
        }
        $stmt->close();
    }

    // If no errors, hash the password and insert into the database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $hashedPassword, $role);

        if ($stmt->execute()) {
            // Instead of a direct header redirect, show a pop-up then redirect via JavaScript.
            echo '<script>
                    alert("Signup Successful");
                    window.location.href = "login.php";
                  </script>';
            exit;
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SwiftMove</title>
    <!-- Link to the separate CSS file for the registration page -->
    <link rel="stylesheet" href="../public/css/register.css">
</head>

<body>
    <div class="container">
        <h2>Register</h2>
        <a href="../public/index.php" class="back-btn">← Back to Home</a>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error) {
                    echo "<p>$error</p>";
                } ?>
            </div>
        <?php endif; ?>

        <form id="registrationForm" method="post" action="register.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <span id="emailFeedback"></span>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                <span id="phoneFeedback"></span>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="role">Register as:</label>
                <select id="role" name="role" required>
                    <option value="customer">Customer</option>
                    <option value="rider">Rider</option>
                </select>
            </div>

            <!-- Rider-specific fields -->
            <div id="riderFields" style="display: none;">
                <div class="form-group">
                    <label for="vehicle_details">Vehicle Details:</label>
                    <input type="text" id="vehicle_details" name="vehicle_details">
                </div>
                <div class="form-group">
                    <label for="license_image">Upload License:</label>
                    <input type="file" id="license_image" name="license_image">
                </div>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>

    <!-- Include client-side form validation script -->
    <script src="../public/js/form-validation.js"></script>
    <script>
        // Toggle rider-specific fields based on the role selection
        document.getElementById('role').addEventListener('change', function() {
            var riderFields = document.getElementById('riderFields');
            riderFields.style.display = (this.value === 'rider') ? 'block' : 'none';
        });
    </script>
</body>

</html>

<?php include_once '../includes/footer.php'; ?>