<?php
// File: auth/login.php
session_start();
include_once '../includes/db.php';
include_once '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize input values
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);

    // Initialize an errors array for validation feedback
    $errors = [];

    if (empty($login)) {
        $errors[] = "Email or phone is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Prepare a statement to search for the user by email or phone
        $stmt = $conn->prepare("SELECT user_id, name, email, phone, password, role FROM users WHERE email = ? OR phone = ? LIMIT 1");
        $stmt->bind_param("ss", $login, $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify the provided password with the stored hash
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // Redirect based on user role
                if ($user['role'] == 'admin') {
                    header("Location: ../dashboard/admin/overview.php");
                } elseif ($user['role'] == 'rider') {
                    header("Location: ../dashboard/rider/overview.php");
                } else {
                    header("Location: ../dashboard/customer/overview.php");
                }
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No user found with this email or phone.";
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
    <title>Login - SwiftMove</title>
    <!-- Link to the separate CSS file for the login page -->
    <link rel="stylesheet" href="../public/css/login.css">
</head>

<body>
    <div class="container">
        <!-- Back Button -->
        <a href="../public/index.php" class="back-btn">← Back to Home</a>

        <h2>Login</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error) {
                    echo "<p>$error</p>";
                } ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="post" action="login.php">
            <div class="form-group">
                <label for="login">Email or Phone:</label>
                <input type="text" id="login" name="login" required value="<?php echo isset($login) ? htmlspecialchars($login) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register Here</a></p>
    </div>

    <script src="../public/js/login-validation.js"></script>
</body>

</html>

<?php include_once '../includes/footer.php'; ?>