
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - SwiftMove</title>
    <link rel="stylesheet" href="css/about.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <header class="site-header">
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">SwiftMove</a>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="about.php" class="active"><i class="fa-solid fa-info-circle"></i> About Us</a></li>
                <li><a href="services.php"><i class="fa-solid fa-box"></i> Services</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-phone"></i> Contact Us</a></li>
            </ul>
            <div class="auth-buttons">
                <a class="btn signup" href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> Sign Up</a>
                <a class="btn login" href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            </div>
            <button class="hamburger" id="hamburger">&#9776;</button>
        </nav>
    </header>

    <main class="about-us">
        <div class="container">
            <h2>About SwiftMove</h2>
            <p>Welcome to SwiftMove – your trusted partner in fast, reliable delivery solutions.</p>
            <div class="content">
                <h3>Our Mission</h3>
                <p>We aim to revolutionize logistics by providing seamless, real-time delivery services that put our customers first.</p>
                <h3>Our Vision</h3>
                <p>Our vision is to create a future where every package is delivered securely and on time, every time.</p>
                <h3>Our Values</h3>
                <ul>
                    <li>Reliability</li>
                    <li>Efficiency</li>
                    <li>Customer-Centricity</li>
                    <li>Innovation</li>
                </ul>
            </div>
        </div>
    </main>

   

    <script>
        document.getElementById('hamburger').addEventListener('click', function() {
            var navLinks = document.getElementById('navLinks');
            if (navLinks.style.display === "flex") {
                navLinks.style.display = "none";
            } else {
                navLinks.style.display = "flex";
                navLinks.style.flexDirection = "column";
            }
        });
    </script>
</body>

</html>