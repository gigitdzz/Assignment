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

// Default values
$totalAppointments = 0;
$upcomingAppointments = 0;
$checkedInAppointments = 0;
$cancelledAppointments = 0;
$nextAppointment = null;
$recentAppointments = [];
$error_message = "";

// 1. Total appointments
$sqlTotal = "SELECT COUNT(*) AS total FROM appointments WHERE user_id = ?";
$stmtTotal = $conn->prepare($sqlTotal);

if ($stmtTotal) {
    $stmtTotal->bind_param("i", $user_id);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    if ($row = $resultTotal->fetch_assoc()) {
        $totalAppointments = (int) $row['total'];
    }
    $stmtTotal->close();
} else {
    $error_message = "Unable to load dashboard statistics.";
}

// 2. Upcoming appointments
$sqlUpcoming = "
    SELECT COUNT(*) AS upcoming
    FROM appointments
    WHERE user_id = ?
      AND appointment_date >= CURDATE()
      AND (status IS NULL OR status NOT IN ('Cancelled'))
";
$stmtUpcoming = $conn->prepare($sqlUpcoming);

if ($stmtUpcoming) {
    $stmtUpcoming->bind_param("i", $user_id);
    $stmtUpcoming->execute();
    $resultUpcoming = $stmtUpcoming->get_result();
    if ($row = $resultUpcoming->fetch_assoc()) {
        $upcomingAppointments = (int) $row['upcoming'];
    }
    $stmtUpcoming->close();
}

// 3. Checked-in appointments
$sqlCheckedIn = "
    SELECT COUNT(*) AS checked_in
    FROM appointments
    WHERE user_id = ?
      AND status = 'Checked In'
";
$stmtCheckedIn = $conn->prepare($sqlCheckedIn);

if ($stmtCheckedIn) {
    $stmtCheckedIn->bind_param("i", $user_id);
    $stmtCheckedIn->execute();
    $resultCheckedIn = $stmtCheckedIn->get_result();
    if ($row = $resultCheckedIn->fetch_assoc()) {
        $checkedInAppointments = (int) $row['checked_in'];
    }
    $stmtCheckedIn->close();
}

// 4. Cancelled appointments
$sqlCancelled = "
    SELECT COUNT(*) AS cancelled
    FROM appointments
    WHERE user_id = ?
      AND status = 'Cancelled'
";
$stmtCancelled = $conn->prepare($sqlCancelled);

if ($stmtCancelled) {
    $stmtCancelled->bind_param("i", $user_id);
    $stmtCancelled->execute();
    $resultCancelled = $stmtCancelled->get_result();
    if ($row = $resultCancelled->fetch_assoc()) {
        $cancelledAppointments = (int) $row['cancelled'];
    }
    $stmtCancelled->close();
}

// 5. Next upcoming appointment
$sqlNext = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
           d.doctor_name, d.specialty
    FROM appointments a
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.user_id = ?
      AND a.appointment_date >= CURDATE()
      AND (a.status IS NULL OR a.status NOT IN ('Cancelled'))
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
";
$stmtNext = $conn->prepare($sqlNext);

if ($stmtNext) {
    $stmtNext->bind_param("i", $user_id);
    $stmtNext->execute();
    $resultNext = $stmtNext->get_result();
    if ($resultNext->num_rows > 0) {
        $nextAppointment = $resultNext->fetch_assoc();
    }
    $stmtNext->close();
}

// 6. Recent appointments list
$sqlRecent = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
           d.doctor_name, d.specialty
    FROM appointments a
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 5
";
$stmtRecent = $conn->prepare($sqlRecent);

if ($stmtRecent) {
    $stmtRecent->bind_param("i", $user_id);
    $stmtRecent->execute();
    $resultRecent = $stmtRecent->get_result();

    while ($row = $resultRecent->fetch_assoc()) {
        $recentAppointments[] = $row;
    }

    $stmtRecent->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Patient Dashboard
</div>

<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Manage your appointments and patient account</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="doctors.php">Doctors</a>
        <a href="book_appointment.php">Book Appointment</a>
        <a href="appointment_history.php">Appointment History</a>
        <a href="checkin.php">Check In</a>
        <a href="cancel.php">Cancel</a>
        <a href="reschedule.php">Reschedule</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="page-container">
    <div class="page-card">
        <h2 class="page-title">Welcome, <?php echo $full_name; ?></h2>
        <p class="page-intro">
            This dashboard gives you a summary of your appointments and quick access to key actions.
        </p>

        <?php if (!empty($error_message)): ?>
            <div class="message message-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <div class="dashboard-box">
                <h3>Total Appointments</h3>
                <p><?php echo $totalAppointments; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Upcoming</h3>
                <p><?php echo $upcomingAppointments; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Checked In</h3>
                <p><?php echo $checkedInAppointments; ?></p>
            </div>

            <div class="dashboard-box">
                <h3>Cancelled</h3>
                <p><?php echo $cancelledAppointments; ?></p>
            </div>
        </div>

        <div class="button-row">
            <a href="book_appointment.php" class="btn-primary">Book Appointment</a>
            <a href="appointment_history.php" class="btn-secondary">View Full History</a>
        </div>

        <h3 style="margin-top: 30px; color:#005eb8;">Next Appointment</h3>

        <?php if ($nextAppointment): ?>
            <div class="message message-info">
                <strong>Doctor:</strong> <?php echo htmlspecialchars($nextAppointment['doctor_name']); ?><br>
                <strong>Specialty:</strong> <?php echo htmlspecialchars($nextAppointment['specialty']); ?><br>
                <strong>Date:</strong> <?php echo htmlspecialchars($nextAppointment['appointment_date']); ?><br>
                <strong>Time:</strong> <?php echo htmlspecialchars($nextAppointment['appointment_time']); ?><br>
                <strong>Status:</strong> <?php echo htmlspecialchars($nextAppointment['status'] ?? 'Booked'); ?>
            </div>
        <?php else: ?>
            <div class="message message-info">
                You do not currently have any upcoming appointments.
            </div>
        <?php endif; ?>

        <h3 style="margin-top: 30px; color:#005eb8;">Recent Appointments</h3>

        <?php if (!empty($recentAppointments)): ?>
            <div class="table-wrapper">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Doctor</th>
                            <th>Specialty</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAppointments as $appointment): ?>
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
            <div class="message message-info">
                No appointments found yet.
            </div>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>