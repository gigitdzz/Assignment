<?php
/*
========================================
File: faq.php
Version: 1.0
Changes from previous version:
- Created FAQ page
- Added common user questions related to registration and login
- Added shared layout and navigation
========================================
*/

// Start session so navigation can adapt to login state
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        NHS Appointment Booking System | Web Design and Development Project
    </div>

    <header class="navbar">
        <div class="logo-section">
            <h1>NHS Booking</h1>
            <p>Simple, secure, and accessible patient account management</p>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="faq.php">FAQ</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="page-container">
        <div class="page-card">
            <h2 class="page-title">Frequently Asked Questions</h2>
            <p class="page-intro">
                Below are some common questions users may have when using the system.
            </p>

            <div class="faq-list">
                <div class="faq-item">
                    <h3>How do I create a new account?</h3>
                    <p>
                        Go to the Register page, complete your personal details, add your NHS Number,
                        email address, phone number, and password, then submit the form.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>What do I use to log in?</h3>
                    <p>
                        You log in using your registered email address and password.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>Why is my NHS Number required?</h3>
                    <p>
                        The NHS Number is included as a unique patient identifier to reflect
                        real-world healthcare system requirements.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>Can two users share the same phone number?</h3>
                    <p>
                        Yes. A phone number is required, but it is not unique, because family
                        members may share the same contact number.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>What happens if I enter the wrong login details?</h3>
                    <p>
                        The system shows a general error message to protect account security.
                    </p>
                </div>
            </div>

            <div class="button-row">
                <a href="index.php" class="btn-primary">Back to Home</a>
                <a href="contact.php" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>
</body>
</html>