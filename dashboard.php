<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

$totalAppointments = 0;
$upcomingAppointments = 0;
$recentAppointments = [];

// Count total appointments
$countSql = "SELECT COUNT(*) AS total FROM appointments WHERE user_id = ?";
$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $user_id);
$countStmt->execute();
$countResult = $countStmt->get_result();
if ($countRow = $countResult->fetch_assoc()) {
    $totalAppointments = $countRow['total'];
}
$countStmt->close();

// Count upcoming appointments
$upcomingSql = "SELECT COUNT(*) AS upcoming
                FROM appointments
                WHERE user_id = ? AND appointment_date >= CURDATE()";
$upcomingStmt = $conn->prepare($upcomingSql);
$upcomingStmt->bind_param("i", $user_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
if ($upcomingRow = $upcomingResult->fetch_assoc()) {
    $upcomingAppointments = $upcomingRow['upcoming'];
}
$upcomingStmt->close();

// Get latest appointments with doctor info
$listSql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
                   d.doctor_name, d.specialty
            FROM appointments a
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.user_id = ?
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 5";

$listStmt = $conn->prepare($listSql);
$listStmt->bind_param("i", $user_id);
$listStmt->execute();
$listResult = $listStmt->get_result();

while ($row = $listResult->fetch_assoc()) {
    $recentAppointments[] = $row;
}
$listStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-bar">
        NHS Appointment Booking System | Patient Dashboard
    </div>

    <header class="navbar">
        <div class="logo-section">
            <h1>NHS Booking</h1>
            <p>Manage your appointments and account</p>
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
                Use your dashboard to review your appointments and quickly access key actions.
            </p>

            <div class="dashboard-grid">
                <div class="dashboard-box">
                    <h3>Total Appointments</h3>
                    <p><?php echo (int)$totalAppointments; ?></p>
                </div>

                <div class="dashboard-box">
                    <h3>Upcoming Appointments</h3>
                    <p><?php echo (int)$upcomingAppointments; ?></p>
                </div>
            </div>

            <div class="button-row">
                <a href="book_appointment.php" class="btn-primary">Book Appointment</a>
                <a href="appointment_history.php" class="btn-secondary">View Full History</a>
            </div>

            <h3 style="margin-top: 30px; color:#005eb8;">Recent / Upcoming Appointments</h3>

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
                                    <td><?php echo htmlspecialchars($appointment['status']); ?></td>
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