<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SwiftMove - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <!-- Header and Navigation -->
    <header class="site-header">
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">SwiftMove</a>
            </div>
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="about.php"><i class="fa-solid fa-info-circle"></i> About Us</a></li>
                <li><a href="services.php"><i class="fa-solid fa-box"></i> Services</a></li>
                <li><a href="contact.php"><i class="fa-solid fa-phone"></i> Contact Us</a></li>
            </ul>
            <div class="auth-buttons">
                <a class="btn signup" href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> Sign Up</a>
                <a class="btn login" href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            </div>
            <button class="hamburger" id="hamburger">&#9776;</button>
        </nav>

        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="animate__animated animate__fadeInDown">SwiftMove</h1>
                <p class="animate__animated animate__fadeInUp">Fast, Reliable Delivery Solutions</p>
                <a href="../auth/register.php" class="btn cta animate__animated animate__zoomIn"><i class="fa-solid fa-rocket"></i> Get Started</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <!-- Services Section -->
        <section class="services">
            <h2>Our Delivery Options</h2>
            <div class="service-cards">
                <div class="card">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>Standard Delivery</h3>
                    <p>Delivery within 24-48 hours for your everyday needs.</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-shipping-fast"></i>
                    <h3>Express Delivery</h3>
                    <p>Fast delivery within 3-6 hours when you need it urgently.</p>
                </div>
                <div class="card">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>Scheduled Delivery</h3>
                    <p>Book a specific time slot for deliveries at your convenience.</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <h2>What Our Customers Say</h2>
            <div class="testimonial-cards">
                <div class="testimonial">
                    <p>"SwiftMove is amazing! My packages always arrive on time and in perfect condition."</p>
                    <span>- Jane Doe</span>
                </div>
                <div class="testimonial">
                    <p>"Reliable, fast, and professional service. Highly recommended."</p>
                    <span>- John Smith</span>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>&copy; <?php echo date("Y"); ?> SwiftMove. All rights reserved.</p>
    </footer>

    <!-- JavaScript for Mobile Navigation -->
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
    <!-- Optionally, include Animate.css for additional animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</body>

</html>