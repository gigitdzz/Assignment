<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Europe/London');

$user_id = (int)$_SESSION['user_id'];
$message = "";
$message_class = "";
$todayAppointments = [];

$sql = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.appointment_type, a.status,
           d.doctor_name, d.specialty, d.clinic_name, d.room_number
    FROM appointments a
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.user_id = ?
      AND a.appointment_date = CURDATE()
      AND a.status <> 'Cancelled'
    ORDER BY a.appointment_time ASC
";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $todayAppointments[] = $row;
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_id = isset($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : 0;

    if ($appointment_id <= 0) {
        $message = "Please select a valid appointment.";
        $message_class = "message message-error";
    } else {
        $check = $conn->prepare("
            SELECT appointment_id, status
            FROM appointments
            WHERE appointment_id = ?
              AND user_id = ?
              AND appointment_date = CURDATE()
              AND status <> 'Cancelled'
            LIMIT 1
        ");
        $check->bind_param("ii", $appointment_id, $user_id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows === 0) {
            $message = "That appointment is not available for check-in today.";
            $message_class = "message message-error";
        } else {
            $row = $checkResult->fetch_assoc();

            if ($row['status'] === 'Checked In') {
                $message = "This appointment has already been checked in.";
                $message_class = "message message-info";
            } else {
                $update = $conn->prepare("
                    UPDATE appointments
                    SET status = 'Checked In'
                    WHERE appointment_id = ? AND user_id = ?
                ");
                $update->bind_param("ii", $appointment_id, $user_id);

                if ($update->execute()) {
                    $message = "Appointment checked in successfully.";
                    $message_class = "message message-success";
                } else {
                    $message = "Error checking in appointment.";
                    $message_class = "message message-error";
                }

                $update->close();
            }
        }

        $check->close();
    }

    // Reload today's appointments after update
    $todayAppointments = [];
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $todayAppointments[] = $row;
        }

        $stmt->close();
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
    NHS Appointment Booking System | Check In
</div>

<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Check in for appointments scheduled today</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="appointment_history.php">Appointment History</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">
    <div class="page-card booking-card">
        <h2 class="page-title">Check In</h2>
        <p class="page-intro">
            Select one of your appointments scheduled for today and check in.
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($todayAppointments)): ?>
            <form method="POST">
                <div class="form-group full-width">
                    <label for="appointment_id">Today's Appointments</label>
                    <select name="appointment_id" id="appointment_id" required>
                        <option value="">Choose an appointment</option>
                        <?php foreach ($todayAppointments as $appt): ?>
                            <option value="<?php echo (int)$appt['appointment_id']; ?>">
                                <?php echo htmlspecialchars(
                                    '#' . $appt['appointment_id'] . ' - ' .
                                    $appt['doctor_name'] . ' - ' .
                                    $appt['clinic_name'] . ' - ' .
                                    substr($appt['appointment_time'], 0, 5) . ' - ' .
                                    $appt['appointment_type'] . ' - ' .
                                    $appt['status']
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Check In</button>
                    <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
                </div>
            </form>

            <div class="table-wrapper" style="margin-top: 25px;">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Clinician</th>
                            <th>Clinic</th>
                            <th>Room</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayAppointments as $appt): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appt['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appt['clinic_name']); ?></td>
                                <td><?php echo htmlspecialchars($appt['room_number']); ?></td>
                                <td><?php echo htmlspecialchars(substr($appt['appointment_time'], 0, 5)); ?></td>
                                <td><?php echo htmlspecialchars($appt['appointment_type']); ?></td>
                                <td><?php echo htmlspecialchars($appt['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message message-info">
                You do not have any appointments scheduled for today.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>