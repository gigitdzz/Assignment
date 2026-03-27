<?php
/*
========================================
File: contact.php
Version: 1.0
Changes from previous version:
- Created Contact page
- Added simple contact form
- Added placeholder success message for future backend integration
- Added shared layout and navigation
========================================
*/

// Start session so navigation can adapt to login state
session_start();

// Message variable used after form submission
$message = "";

// Check whether the contact form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // This is currently a front-end demonstration message
    $message = "Thank you for your message. This contact form is currently a front-end demonstration and can be connected to a backend later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
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
            <h2 class="page-title">Contact Us</h2>
            <p class="page-intro">
                If you have any questions about the system, you can use the form below.
            </p>

            <?php if (!empty($message)) echo "<div class='message'>" . htmlspecialchars($message) . "</div>"; ?>

            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="message_box">Message</label>
                        <textarea id="message_box" name="message_box" required></textarea>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Send Message</button>
                    <a href="index.php" class="btn-secondary">Back to Home</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>
</body>
</html>