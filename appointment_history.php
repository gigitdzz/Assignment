<?php
session_start();
require_once 'db_connection.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$full_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$appointments = [];
$error_message = "";

// Load all appointments for this user
$sql = "
    SELECT a.appointment_id,
           a.appointment_date,
           a.appointment_time,
           a.status,
           d.doctor_name,
           d.specialty
    FROM appointments a
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    $stmt->close();
} else {
    $error_message = "Unable to load appointment history.";
}

// Optional stats for summary
$totalAppointments = count($appointments);
$bookedCount = 0;
$checkedInCount = 0;
$cancelledCount = 0;
$rescheduledCount = 0;

foreach ($appointments as $appointment) {
    $status = strtolower(trim($appointment['status'] ?? 'Booked'));

    if ($status === 'booked') {
        $bookedCount++;
    } elseif ($status === 'checked in') {
        $checkedInCount++;
    } elseif ($status === 'cancelled') {
        $cancelledCount++;
    } elseif ($status === 'rescheduled') {
        $rescheduledCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Appointment History
</div>

<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Review your full appointment record</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="doctors.php">Doctors</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="checkin.php">Check In</a>
        <a href="cancel.php">Cancel</a>
        <a href="reschedule.php">Reschedule</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">
    <div class="page-card">
        <h2 class="page-title">Appointment History</h2>
        <p class="page-intro">
            Logged in as <?php echo $full_name; ?>. This page shows your full appointment history.
        </p>

        <?php if (!empty($error_message)): ?>
            <div class="message message-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-box">
                <h3>Total</h3>
                <p><?php echo $totalAppointments; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Booked</h3>
                <p><?php echo $bookedCount; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Checked In</h3>
                <p><?php echo $checkedInCount; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Cancelled / Rescheduled</h3>
                <p><?php echo $cancelledCount + $rescheduledCount; ?></p>
            </div>
        </div>

        <div class="button-row">
            <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
            <a href="book_appointment.php" class="btn-primary">Book New Appointment</a>
        </div>

        <?php if (!empty($appointments)): ?>
            <div class="table-wrapper" style="margin-top: 25px;">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>Doctor</th>
                            <th>Specialty</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['status'] ?? 'Booked'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message message-info" style="margin-top: 25px;">
                No appointment history is available yet.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>