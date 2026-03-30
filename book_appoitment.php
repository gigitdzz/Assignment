<?php
session_start();
include 'db_connection.php';

$message = "";
$message_class = "";
$selected_doctor_id = "";

if (isset($_GET['doctor_id']) && !empty($_GET['doctor_id'])) {
    $selected_doctor_id = $_GET['doctor_id'];
}

$doctors_sql = "SELECT doctor_id, doctor_name, specialty FROM doctors ORDER BY doctor_name ASC";
$doctors_result = $conn->query($doctors_sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = trim($_POST['doctor_id']);
    $appointment_date = trim($_POST['appointment_date']);
    $appointment_time = trim($_POST['appointment_time']);

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        $user_id = 1;
    }

    if (!empty($doctor_id) && !empty($appointment_date) && !empty($appointment_time)) {

        if ($appointment_date < date('Y-m-d')) {
            $message = "You cannot book an appointment in the past.";
            $message_class = "message message-info";
        } else {
            $check_sql = "SELECT appointment_id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iss", $doctor_id, $appointment_date, $appointment_time);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $message = "This time slot is no longer available. Please choose another date or time.";
                $message_class = "message message-info";
            } else {
                $insert_sql = "INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iiss", $user_id, $doctor_id, $appointment_date, $appointment_time);

                if ($stmt->execute()) {
                    $doctor_name_sql = "SELECT doctor_name FROM doctors WHERE doctor_id = ?";
                    $doctor_stmt = $conn->prepare($doctor_name_sql);
                    $doctor_stmt->bind_param("i", $doctor_id);
                    $doctor_stmt->execute();
                    $doctor_result = $doctor_stmt->get_result();
                    $doctor_data = $doctor_result->fetch_assoc();

                    $message = "Your appointment with " . $doctor_data['doctor_name'] . " has been booked for " . $appointment_date . " at " . $appointment_time . ".";
                    $message_class = "message message-success";
                } else {
                    $message = "Error booking appointment.";
                    $message_class = "message message-info";
                }
            }
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
    <title>Book Appointment</title>
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
        <div class="page-card booking-card">
            <h2 class="page-title">Book an Appointment</h2>
            <p class="page-intro">
                Choose a doctor, select a date, and confirm your appointment time.
            </p>

            <?php if (!empty($message)): ?>
                <div class="<?php echo $message_class; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="doctor_id">Select Doctor</label>
                        <select name="doctor_id" id="doctor_id" required>
                            <option value="">Choose a doctor</option>
                            <?php if ($doctors_result && $doctors_result->num_rows > 0): ?>
                                <?php while ($doctor = $doctors_result->fetch_assoc()): ?>
                                    <option value="<?php echo $doctor['doctor_id']; ?>" <?php echo ($selected_doctor_id == $doctor['doctor_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($doctor['doctor_name'] . ' - ' . $doctor['specialty']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointment_date">Appointment Date</label>
                        <input type="date" name="appointment_date" id="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="appointment_time">Appointment Time</label>
                        <input type="time" name="appointment_time" id="appointment_time" required>
                    </div>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Book Appointment</button>
                    <a href="doctors.php" class="btn-secondary">Back to Doctors</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>
