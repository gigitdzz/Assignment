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
    $new_date = trim($_POST['new_date'] ?? '');
    $new_time = trim($_POST['new_time'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($appointment_id > 0 && !empty($new_date) && !empty($new_time) && !empty($password)) {
        $user_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $user_row = $user_result->fetch_assoc();
            $stored_password = $user_row['password'];

            if ($password === $stored_password || password_verify($password, $stored_password)) {
                $stmt = $conn->prepare("SELECT appointment_id FROM appointments WHERE appointment_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $appointment_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $update = $conn->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, status = 'Rescheduled' WHERE appointment_id = ? AND user_id = ?");
                    $update->bind_param("ssii", $new_date, $new_time, $appointment_id, $user_id);

                    if ($update->execute()) {
                        $message = "Appointment rescheduled successfully.";
                        $message_class = "message message-success";
                    } else {
                        $message = "Error rescheduling appointment.";
                        $message_class = "message message-info";
                    }
                } else {
                    $message = "No appointment found with that ID.";
                    $message_class = "message message-info";
                }
            } else {
                $message = "Incorrect password.";
                $message_class = "message message-info";
            }
        } else {
            $message = "User not found.";
            $message_class = "message message-info";
        }
    } else {
        $message = "Please fill in all fields.";
        $message_class = "message message-info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment</title>
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
            <h2 class="page-title">Reschedule Appointment</h2>
            <p class="page-intro">
                Enter your appointment ID, choose a new date and time, and confirm your password.
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

                    <div class="form-group">
                        <label for="new_date">New Appointment Date</label>
                        <input type="date" name="new_date" id="new_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="new_time">New Appointment Time</label>
                        <input type="time" name="new_time" id="new_time" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="password">Confirm Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Reschedule Appointment</button>
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