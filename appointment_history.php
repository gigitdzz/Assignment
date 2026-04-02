<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
$appointments = [];

$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status,
               d.doctor_name, d.specialty
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.doctor_id
        WHERE a.user_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
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
            <p>Review all of your booked appointments</p>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="doctors.php">Doctors</a>
            <a href="book_appointment.php">Book Appointment</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <main class="page-container">
        <div class="page-card">
            <h2 class="page-title">Appointment History</h2>
            <p class="page-intro">
                Logged in as <?php echo $full_name; ?>. Below is your full appointment record.
            </p>

            <div class="button-row">
                <a href="dashboard.php" class="btn-secondary">Back to Dashboard</a>
            </div>

            <?php if (!empty($appointments)): ?>
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
                            <?php foreach ($appointments as $appointment): ?>
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