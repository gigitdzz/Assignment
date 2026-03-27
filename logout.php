<?php
/*
========================================
File: logout.php
Version: 2.0
Changes from previous version:
- Replaced immediate redirect with a logout confirmation page
- Added polite sign-out message
- Added buttons to return to Home or sign in again
- Added shared layout and navigation
========================================
*/

// Start the session before clearing it
session_start();

// Remove all session variables
session_unset();

// Destroy the current session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signed Out</title>
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
            <a href="register.php">Register</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <main class="page-container">
        <div class="page-card">
            <h2 class="page-title">You Have Been Signed Out</h2>
            <p class="page-intro">
                Thank you for using the NHS Appointment Booking System.
                You have logged out successfully and your session has ended securely.
            </p>

            <div class="message">
                If you need to continue using the system, you may return to the home page or sign in again.
            </div>

            <div class="button-row">
                <a href="index.php" class="btn-primary">Return to Home</a>
                <a href="login.php" class="btn-secondary">Login Again</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>
</body>
</html>