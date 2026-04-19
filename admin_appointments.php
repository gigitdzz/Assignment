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
$message = "";
$message_class = "";
$appointments = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $appointment_id = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
        $new_status = trim($_POST['new_status'] ?? '');

        $allowed_statuses = ['Booked', 'Checked In', 'Cancelled', 'Rescheduled', 'Completed'];

        if ($appointment_id > 0 && in_array($new_status, $allowed_statuses, true)) {
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->bind_param("si", $new_status, $appointment_id);

            if ($stmt->execute()) {
                $message = "Appointment status updated successfully.";
                $message_class = "message message-success";
            } else {
                $message = "Unable to update appointment status.";
                $message_class = "message message-error";
            }
        }
    }

    if ($action === 'delete_appointment') {
        $appointment_id = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;

        if ($appointment_id > 0) {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
            $stmt->bind_param("i", $appointment_id);

            if ($stmt->execute()) {
                $message = "Appointment deleted successfully.";
                $message_class = "message message-success";
            } else {
                $message = "Unable to delete appointment.";
                $message_class = "message message-error";
            }
        }
    }
}

$sqlAppointments = "
    SELECT 
        a.appointment_id,
        a.user_id,
        a.doctor_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        a.created_at,
        u.first_name,
        u.last_name,
        d.doctor_name,
        d.specialty
    FROM appointments a
    INNER JOIN users u ON a.user_id = u.user_id
    INNER JOIN doctors d ON a.doctor_id = d.doctor_id
    ORDER BY a.appointment_id DESC
";
$resultAppointments = $conn->query($sqlAppointments);
if ($resultAppointments) {
    while ($row = $resultAppointments->fetch_assoc()) {
        $appointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Appointments</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Admin Appointments
</div>

<div class="admin-shell">
    <div class="admin-header-card">
        <h2>Admin Panel</h2>
        <p>Welcome, <?php echo $admin_name; ?>. Review and manage appointment records.</p>

        <div class="admin-tabs">
            <a class="admin-tab" href="admin_dashboard.php">Overview</a>
            <a class="admin-tab" href="admin_users.php">Users</a>
            <a class="admin-tab" href="admin_doctors.php">Doctors</a>
            <a class="admin-tab active" href="admin_appointments.php">Appointments</a>
            <a class="admin-tab" href="admin_notifications.php">Notifications</a>
            <a class="admin-tab" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="admin-card">
        <h3 class="admin-section-title">Appointments</h3>
        <p class="admin-section-intro">
            This page allows the administrator to update appointment status and delete appointment records.
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($appointments)): ?>
            <div class="table-wrapper">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Appointment ID</th>
                            <th>User</th>
                            <th>Doctor</th>
                            <th>Specialty</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Admin Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?> (ID: <?php echo htmlspecialchars($appointment['user_id']); ?>)</td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name']); ?> (ID: <?php echo htmlspecialchars($appointment['doctor_id']); ?>)</td>
                                <td><?php echo htmlspecialchars($appointment['specialty']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                <td>
                                    <?php
                                    $status = $appointment['status'] ?? 'Booked';
                                    $status_class = 'status-booked';
                                    if ($status === 'Checked In') $status_class = 'status-checked';
                                    if ($status === 'Cancelled') $status_class = 'status-cancelled';
                                    if ($status === 'Rescheduled') $status_class = 'status-rescheduled';
                                    if ($status === 'Completed') $status_class = 'status-completed';
                                    ?>
                                    <span class="status-pill <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($appointment['created_at']); ?></td>
                                <td>
                                    <div class="admin-actions-inline">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                                            <select name="new_status" class="small-select">
                                                <option value="Booked">Booked</option>
                                                <option value="Checked In">Checked In</option>
                                                <option value="Cancelled">Cancelled</option>
                                                <option value="Rescheduled">Rescheduled</option>
                                                <option value="Completed">Completed</option>
                                            </select>
                                            <button type="submit" class="small-button secondary">Update</button>
                                        </form>

                                        <form method="POST" onsubmit="return confirm('Delete this appointment?');">
                                            <input type="hidden" name="action" value="delete_appointment">
                                            <input type="hidden" name="appointment_id" value="<?php echo (int)$appointment['appointment_id']; ?>">
                                            <button type="submit" class="small-button danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message message-info">No appointments found.</div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>