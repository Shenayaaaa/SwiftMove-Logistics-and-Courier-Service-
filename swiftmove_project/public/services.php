
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Our Services - SwiftMove</title>
    <link rel="stylesheet" href="css/services.css">

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
                <li><a href="about.php"><i class="fa-solid fa-info-circle"></i> About Us</a></li>
                <li><a href="services.php" class="active"><i class="fa-solid fa-box"></i> Services</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-phone"></i> Contact Us</a></li>
            </ul>
            <div class="auth-buttons">
                <a class="btn signup" href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> Sign Up</a>
                <a class="btn login" href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            </div>
            <button class="hamburger" id="hamburger">&#9776;</button>
        </nav>
    </header>

    <main class="services-page">
        <div class="container">
            <h2>Our Services</h2>
            <p>SwiftMove offers a range of delivery solutions to meet your needs.</p>
            <div class="services-cards">
                <div class="card">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>Standard Delivery</h3>
                    <p>Reliable delivery within 24-48 hours for everyday needs.</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-shipping-fast"></i>
                    <h3>Express Delivery</h3>
                    <p>Fast delivery within 3-6 hours when time is critical.</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>Scheduled Delivery</h3>
                    <p>Book a specific time slot that works best for you.</p>
                </div>
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