<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$message_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

    if ($appointment_id > 0) {
        $stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $appointment_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $update = $conn->prepare("UPDATE appointments SET status = 'Checked In' WHERE appointment_id = ? AND user_id = ?");
            $update->bind_param("ii", $appointment_id, $user_id);

            if ($update->execute()) {
                $message = "Appointment checked in successfully.";
                $message_class = "message message-success";
            } else {
                $message = "Error checking in appointment.";
                $message_class = "message message-info";
            }
        } else {
            $message = "No appointment found with that ID.";
            $message_class = "message message-info";
        }
    } else {
        $message = "Please enter a valid appointment ID.";
        $message_class = "message message-info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check In</title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="page-container">
        <div class="page-card booking-card">
            <h2 class="page-title">Check In</h2>
            <p class="page-intro">
                Enter your appointment ID to check in for your booked appointment.
            </p>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $message_class; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="appointment_id">Appointment ID</label>
                        <input type="number" name="appointment_id" id="appointment_id" required>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Check In</button>
                    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>