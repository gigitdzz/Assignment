<?php
/*
========================================
File: about.php
Version: 2.0
Changes from previous version:
- Rebuilt About Us page using shared site styling
- Added complete navigation linked to all main pages
- Added editable project information sections
- Added placeholders for module, lecturer, institution, and team members
- Added structured comments for group work reference
========================================
*/

// Start session so navigation can change based on login status
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Top information bar -->
    <div class="top-bar">
        NHS Appointment Booking System | Web Design and Development Project
    </div>

    <!-- Main navigation header -->
    <header class="navbar">
        <div class="logo-section">
            <h1>NHS Booking</h1>
            <p>Simple, secure, and accessible patient account management</p>
        </div>

        <!-- Navigation links -->
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
            <!-- Main page title -->
            <h2 class="page-title">About This Project</h2>
            <p class="page-intro">
                This page provides background information about the NHS Appointment Booking System,
                including its academic context, development goals, and project team.
            </p>

            <!-- Project summary section -->
            <div class="message">
                <!-- EDIT THIS SECTION -->
                This project was developed as part of a university assessment for the
                <strong>Web Design and Development</strong> module.
            </div>

            <h3 style="color:#005eb8; margin-bottom: 12px;">Project Description</h3>
            <p style="margin-bottom: 14px;">
                <!-- EDIT THIS SECTION -->
                The NHS Appointment Booking System is a web-based project created using
                HTML, CSS, PHP, and MySQL. Its purpose is to provide a simple and secure
                platform where users can register, log in, and access healthcare-related
                services in an NHS-style environment.
            </p>

            <p style="margin-bottom: 24px;">
                <!-- EDIT THIS SECTION -->
                The current version of the system focuses on user registration, secure login,
                password protection, session handling, and database integration. Future
                development may include appointment booking, check-in, appointment history,
                and notification features.
            </p>

            <!-- Academic information section -->
            <h3 style="color:#005eb8; margin-bottom: 12px;">Academic Information</h3>
            <p style="margin-bottom: 10px;">
                <strong>Module Name:</strong> Web Design and Development
            </p>
            <p style="margin-bottom: 10px;">
                <strong>Module Code:</strong> LD4016BLZ01
            </p>
            <p style="margin-bottom: 10px;">
                <strong>Lecturer:</strong> Omer Raza
            </p>
            <p style="margin-bottom: 24px;">
                <strong>Institution:</strong> northumbria university
            </p>

            <!-- Team members section -->
            <h3 style="color:#005eb8; margin-bottom: 12px;">Project Team</h3>
            <p style="margin-bottom: 10px;">
                The following students contributed to the development of this project:
            </p>

            <div class="faq-list" style="margin-bottom: 24px;">
                <div class="faq-item">
                    <h3>Ema</h3>
                    <p>[Add a short description of your contribution here.]</p>
                </div>

                <div class="faq-item">
                    <h3>Gio</h3>
                    <p>[Add a short description of this member's contribution here.]</p>
                </div>

                <div class="faq-item">
                    <h3>Kristian</h3>
                    <p>[Add a short description of this member's contribution here.]</p>
                </div>

                <div class="faq-item">
                    <h3>Camila</h3>
                    <p>This part of the project focuses on the user authentication system and the core frontend structure of the NHS Appointment Booking System. <br>
                        <br>
                        The implementation includes: <br>
                        <br>
                        • User registration <br>
                        • Secure login system <br>
                        • Session handling <br>
                        • Basic logout flow <br>
                        • Supporting pages (FAQ, Contact, About) <br>
                      </p>
                </div>
            </div>

            <!-- Editable note section -->
            <h3 style="color:#005eb8; margin-bottom: 12px;">Editable Notes</h3>
            <p style="margin-bottom: 14px;">
                <!-- EDIT THIS SECTION -->
                You can update this page later by replacing all placeholder text in square
                brackets with your final assessment details, team information, and lecturer name.
            </p>

            <div class="button-row">
                <a href="index.php" class="btn-primary">Back to Index</a>
                <a href="contact.php" class="btn-secondary">Contact Us</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>