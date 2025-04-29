
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us - SwiftMove</title>
    <link rel="stylesheet" href="css/contact.css">
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
                <li><a href="services.php"><i class="fa-solid fa-box"></i> Services</a></li>
                <li><a href="contact.php" class="active"><i class="fa-solid fa-phone"></i> Contact Us</a></li>
            </ul>
            <div class="auth-buttons">
                <a class="btn signup" href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> Sign Up</a>
                <a class="btn login" href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            </div>
            <button class="hamburger" id="hamburger">&#9776;</button>
        </nav>
    </header>

    <main class="contact-us">
        <div class="container">
            <h2>Contact Us</h2>
            <p>Have questions or need support? Reach out to us using the form below or via our contact details.</p>
            <div class="contact-info">
                <p><strong>Address:</strong> 123 SwiftMove Street, Colombo, Sri Lanka</p>
                <p><strong>Phone:</strong> +94 11 2345678</p>
                <p><strong>Email:</strong> support@swiftmove.lk</p>
            </div>
            <div class="contact-form">
                <form action="contact.php" method="post">
                    <div class="form-group">
                        <label for="name">Your Name:</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email:</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message:</label>
                        <textarea id="message" name="message" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
                </form>
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