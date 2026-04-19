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

$totalUsers = 0;
$totalDoctors = 0;
$totalAppointments = 0;
$totalNotifications = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result && $row = $result->fetch_assoc()) {
    $totalUsers = (int)$row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM doctors");
if ($result && $row = $result->fetch_assoc()) {
    $totalDoctors = (int)$row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM appointments");
if ($result && $row = $result->fetch_assoc()) {
    $totalAppointments = (int)$row['total'];
}

$result = $conn->query("SELECT COUNT(*) AS total FROM notifications");
if ($result && $row = $result->fetch_assoc()) {
    $totalNotifications = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Admin Dashboard
</div>

<div class="admin-shell">
    <div class="admin-header-card">
        <h2>Admin Panel</h2>
        <p>Welcome, <?php echo $admin_name; ?>. Manage users, doctors, appointments, and notifications.</p>

        <div class="admin-tabs">
            <a class="admin-tab active" href="admin_dashboard.php">Overview</a>
            <a class="admin-tab" href="admin_users.php">Users</a>
            <a class="admin-tab" href="admin_doctors.php">Doctors</a>
            <a class="admin-tab" href="admin_appointments.php">Appointments</a>
            <a class="admin-tab" href="admin_notifications.php">Notifications</a>
            <a class="admin-tab" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="admin-card">
        <h3 class="admin-section-title">System Overview</h3>
        <p class="admin-section-intro">
            This page provides a summary of the NHS Booking system for the administrator.
        </p>

        <div class="stats-grid">
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Doctors</h3>
                <p><?php echo $totalDoctors; ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Appointments</h3>
                <p><?php echo $totalAppointments; ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Notifications</h3>
                <p><?php echo $totalNotifications; ?></p>
            </div>
        </div>

        <div class="admin-note">
            Use the admin tabs above to move between sections. This keeps the admin area organised and easier to explain in the presentation.
        </div>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>