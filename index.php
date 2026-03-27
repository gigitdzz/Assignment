<?php
/*
========================================
File: index.php
Version: 5.0
Changes from previous version:
- Set index.php as the only official Home page
- Kept the original professional home layout
- Added FAQ and Contact links to the navigation
- Added FAQ and Contact service cards
- Improved consistency with the rest of the website
- Added structured comments for group work reference
========================================
*/

// Start session so the page can detect login status
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHS Appointment Booking System</title>
    <style>
        /* Reset default spacing and use border-box sizing */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Global page styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            color: #1f2937;
            line-height: 1.6;
        }

        /* Remove default underline from links */
        a {
            text-decoration: none;
        }

        /* Top information bar */
        .top-bar {
            background-color: #005eb8;
            color: white;
            padding: 12px 40px;
            font-size: 14px;
        }

        /* Main navigation bar */
        .navbar {
            background-color: white;
            border-bottom: 1px solid #d1d5db;
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Logo and subtitle area */
        .logo-section h1 {
            font-size: 28px;
            color: #005eb8;
            margin-bottom: 4px;
        }

        .logo-section p {
            font-size: 14px;
            color: #6b7280;
        }

        /* Navigation links */
        .nav-links {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #111827;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .nav-links a:hover {
            color: #005eb8;
        }

        /* Hero section wrapper */
        .hero {
            max-width: 1200px;
            margin: 40px auto 30px;
            padding: 0 20px;
        }

        /* Main hero content box */
        .hero-box {
            background: linear-gradient(135deg, #005eb8, #003087);
            color: white;
            border-radius: 14px;
            padding: 50px 40px;
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 30px;
            align-items: center;
        }

        /* Main hero text */
        .hero-text h2 {
            font-size: 42px;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-text p {
            font-size: 18px;
            max-width: 700px;
            margin-bottom: 28px;
            color: #e5eef8;
        }

        /* Hero buttons container */
        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
        }

        /* Shared hero button styling */
        .btn-primary,
        .btn-secondary {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.2s ease;
        }

        /* Primary hero button */
        .btn-primary {
            background-color: white;
            color: #005eb8;
        }

        .btn-primary:hover {
            background-color: #e8f1fb;
        }

        /* Secondary hero button */
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Side information panel inside hero */
        .hero-panel {
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            padding: 24px;
            backdrop-filter: blur(2px);
        }

        .hero-panel h3 {
            margin-bottom: 12px;
            font-size: 24px;
        }

        .hero-panel p {
            color: #e5eef8;
            margin-bottom: 12px;
        }

        /* Shared content section */
        .content-section {
            max-width: 1200px;
            margin: 0 auto 30px;
            padding: 0 20px;
        }

        /* Section heading */
        .section-title {
            font-size: 30px;
            margin-bottom: 20px;
            color: #111827;
        }

        /* Card grid for services */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        /* Individual service card */
        .card {
            background-color: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
            border-top: 5px solid #005eb8;
        }

        .card h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #005eb8;
        }

        .card p {
            color: #4b5563;
            margin-bottom: 16px;
        }

        .card a {
            color: #005eb8;
            font-weight: bold;
        }

        /* Project overview box */
        .project-box {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
        }

        .project-box p {
            margin-bottom: 14px;
            color: #374151;
        }

        /* Footer */
        .footer {
            background-color: #003087;
            color: white;
            margin-top: 40px;
            padding: 24px 20px;
            text-align: center;
        }

        /* Tablet adjustments */
        @media (max-width: 900px) {
            .hero-box {
                grid-template-columns: 1fr;
            }

            .hero-text h2 {
                font-size: 34px;
            }

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                gap: 16px;
            }
        }

        /* Mobile adjustments */
        @media (max-width: 600px) {
            .hero-text h2 {
                font-size: 28px;
            }

            .top-bar,
            .navbar {
                padding-left: 20px;
                padding-right: 20px;
            }

            .hero-box {
                padding: 35px 25px;
            }
        }
    </style>
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

        <!-- Main site navigation -->
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="#services">Services</a>
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

    <main>
        <!-- Hero section -->
        <section class="hero">
            <div class="hero-box">
                <div class="hero-text">
                    <h2>Manage your appointments with confidence</h2>
                    <p>
                        This website provides a simple and secure way for users to register,
                        sign in, and access healthcare-related services within an NHS-style
                        appointment booking system.
                    </p>

                    <!-- Show different actions depending on login status -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="hero-buttons">
                            <a href="dashboard.php" class="btn-primary">Go to Dashboard</a>
                            <a href="logout.php" class="btn-secondary">Logout</a>
                        </div>
                    <?php else: ?>
                        <div class="hero-buttons">
                            <a href="register.php" class="btn-primary">Register as a New User</a>
                            <a href="login.php" class="btn-secondary">Login to Your Account</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick info panel -->
                <div class="hero-panel">
                    <h3>Quick Information</h3>
                    <p>Users can create an account, sign in securely, and access protected pages.</p>
                    <p>The system is built with PHP, MySQL, HTML, and CSS.</p>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <p>
                            Logged in as:
                            <strong>
                                <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
                            </strong>
                        </p>
                    <?php else: ?>
                        <p>You are currently browsing as a guest user.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Services section -->
        <section class="content-section" id="services">
            <h2 class="section-title">Our Services</h2>

            <div class="cards">
                <div class="card">
                    <h3>Register</h3>
                    <p>Create a new patient account with personal details, NHS number, and a secure password.</p>
                    <a href="register.php">Go to Registration</a>
                </div>

                <div class="card">
                    <h3>Login</h3>
                    <p>Access your account securely using your registered email address and password.</p>
                    <a href="login.php">Go to Login</a>
                </div>

                <div class="card">
                    <h3>Dashboard</h3>
                    <p>Access the protected user area after authentication and continue through the secure system flow.</p>
                    <a href="dashboard.php">Open Dashboard</a>
                </div>

                <div class="card">
                    <h3>About Us</h3>
                    <p>Learn more about the project, module details, lecturer, and team members.</p>
                    <a href="about.php">Read More</a>
                </div>

                <div class="card">
                    <h3>FAQ</h3>
                    <p>Read common questions and answers about account creation, login, and system use.</p>
                    <a href="faq.php">Open FAQ</a>
                </div>

                <div class="card">
                    <h3>Contact</h3>
                    <p>Send a message through the contact page for questions related to the project.</p>
                    <a href="contact.php">Go to Contact Page</a>
                </div>
            </div>
        </section>

        <!-- Project overview section -->
        <section class="content-section">
            <h2 class="section-title">Project Overview</h2>

            <div class="project-box">
                <p>
                    This website was developed as part of a university project for an NHS-style
                    appointment booking system. The current version focuses on user registration,
                    secure login, session handling, and database integration.
                </p>

                <p>
                    The system is designed to provide a professional and accessible entry point for
                    patients while supporting future features such as appointment booking, check-in,
                    appointment history, and notifications.
                </p>

                <p>
                    More information about the module, lecturer, project team, and assessment context
                    is available on the About Us page.
                </p>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>