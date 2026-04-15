<?php
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
                    <h3>How do I book an appointment now?</h3>
                    <p>
                        You first complete the medical form by selecting one or more symptoms.
                        The system then recommends a suitable appointment type and staff category,
                        such as GP, nurse, or specialist, and shows only valid available slots.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>Can I choose any time I want?</h3>
                    <p>
                        No. Appointments are now offered only as valid 15-minute slots within staff
                        working hours. Patients cannot book outside clinician schedules or during staff breaks.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>What are the working hours for appointments?</h3>
                    <p>
                        Staff appointments are scheduled between 8:00am and 8:00pm. Break periods are
                        also built into availability, so unavailable times are automatically excluded.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>What types of staff can I be booked with?</h3>
                    <p>
                        Depending on your selected symptoms, the system may recommend a GP, nurse,
                        or specialist. This helps match patients with appropriate care more efficiently.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>Can I see clinic availability before booking?</h3>
                    <p>
                        Yes. The booking page displays clinicians by clinic and shows available times only,
                        without exposing any other patient information.
                    </p>
                </div>

                <div class="faq-item">
                    <h3>Can I still reschedule or check in?</h3>
                    <p>
                        Yes. Rescheduling now follows the same scheduling rules as booking, and check-in
                        is limited to appointments scheduled for the current day.
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