<?php
session_start();
include 'db_connection.php';

$sql = "SELECT * FROM doctors ORDER BY doctor_name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors</title>
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
            <a href="doctors.php">Doctors</a>

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
            <h2 class="page-title">Available Doctors</h2>
            <p class="page-intro">
                Below is a list of available doctors and their specialisations. You can review the doctor details and continue directly to the booking form.
            </p>

            <div class="faq-list">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($doctor = $result->fetch_assoc()): ?>
                        <div class="faq-item">
                            <h3><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                            <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?></p>

                            <?php if (!empty($doctor['email'])): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($doctor['phone'])): ?>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($doctor['available_days'])): ?>
                                <p><strong>Available Days:</strong> <?php echo htmlspecialchars($doctor['available_days']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($doctor['room_number'])): ?>
                                <p><strong>Room Number:</strong> <?php echo htmlspecialchars($doctor['room_number']); ?></p>
                            <?php endif; ?>

                            <div class="button-row">
                                <a href="book_appointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn-primary">Book This Doctor</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="message message-info">No doctors are currently available.</div>
                <?php endif; ?>
            </div>

            <div class="button-row">
                <a href="index.php" class="btn-secondary">Back to Home</a>
                <a href="book_appointment.php" class="btn-primary">Go to Booking Form</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>
