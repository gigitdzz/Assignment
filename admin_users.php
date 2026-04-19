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

$admin_id = (int) $_SESSION['user_id'];
$admin_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

$message = "";
$message_class = "";
$users = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $target_user_id = isset($_POST['target_user_id']) ? (int) $_POST['target_user_id'] : 0;
    $new_role = trim($_POST['new_role'] ?? '');

    if ($target_user_id > 0 && in_array($new_role, ['patient', 'admin'], true)) {
        if ($target_user_id === $admin_id && $new_role !== 'admin') {
            $message = "You cannot remove your own admin role.";
            $message_class = "message message-error";
        } else {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_role, $target_user_id);

            if ($stmt->execute()) {
                $message = "User role updated successfully.";
                $message_class = "message message-success";
            } else {
                $message = "Unable to update user role.";
                $message_class = "message message-error";
            }
        }
    }
}

$sqlUsers = "
    SELECT user_id, nhs_number, first_name, last_name, email, phone, date_of_birth, role, created_at
    FROM users
    ORDER BY user_id DESC
";
$resultUsers = $conn->query($sqlUsers);
if ($resultUsers) {
    while ($row = $resultUsers->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="top-bar">
    NHS Appointment Booking System | Admin Users
</div>

<div class="admin-shell">
    <div class="admin-header-card">
        <h2>Admin Panel</h2>
        <p>Welcome, <?php echo $admin_name; ?>. Review users and manage roles.</p>

        <div class="admin-tabs">
            <a class="admin-tab" href="admin_dashboard.php">Overview</a>
            <a class="admin-tab active" href="admin_users.php">Users</a>
            <a class="admin-tab" href="admin_doctors.php">Doctors</a>
            <a class="admin-tab" href="admin_appointments.php">Appointments</a>
            <a class="admin-tab" href="admin_notifications.php">Notifications</a>
            <a class="admin-tab" href="logout.php">Logout</a>
        </div>
    </div>

    <div class="admin-card">
        <h3 class="admin-section-title">Users</h3>
        <p class="admin-section-intro">
            This page allows the administrator to review all users and update account roles.
        </p>

        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($users)): ?>
            <div class="table-wrapper">
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>NHS Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date of Birth</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Admin Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($user['nhs_number']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td>
                                    <form method="POST" class="admin-actions-inline">
                                        <input type="hidden" name="target_user_id" value="<?php echo (int)$user['user_id']; ?>">
                                        <select name="new_role" class="small-select">
                                            <option value="patient" <?php echo $user['role'] === 'patient' ? 'selected' : ''; ?>>patient</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>admin</option>
                                        </select>
                                        <button type="submit" class="small-button primary">Update Role</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message message-info">No users found.</div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

</body>
</html>