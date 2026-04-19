<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$admin_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$doctors = [];

$sqlDoctors = "
    SELECT doctor_id, doctor_name, specialty, email, phone, available_days, room_number
    FROM doctors
    ORDER BY doctor_id DESC
";
$resultDoctors = $conn->query($sqlDoctors);
if ($resultDoctors) {
    while ($row = $resultDoctors->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Doctors</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Admin Doctors
</div>

<div class="admin-shell">
    <div class="admin-header-card">
        <h2>Admin Panel</h2>
        <p>Welcome, <?php echo $admin_name; ?>. Review all doctors in the system.</p>

        <div class="admin-tabs">
            <a class="admin-tab" href="admin_dashboard.php">Overview</a>
            <a class="admin-tab" href="admin_users.php">Users</a>
            <a class="admin-tab active" href="admin_doctors.php">Doctors</a>
            <a class="admin-tab" href="admin_appointments.php">Appointments</a>
            <a class="admin-tab" href="admin_notifications.php">Notifications</a>
            <a class="admin-tab" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="admin-card">
        <h3 class="admin-section-title">Doctors</h3>
        <p class="admin-section-intro">
            This page shows all doctors currently stored in the system database.
        </p>

        <?php if (!empty($doctors)): ?>
            <div class="table-wrapper">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Doctor ID</th>
                            <th>Name</th>
                            <th>Specialty</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Available Days</th>
                            <th>Room Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['doctor_id']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialty']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['available_days']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['room_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message message-info">No doctors found.</div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>