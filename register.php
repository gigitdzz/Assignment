<?php
/*
========================================
File: register.php
Version: 3.1
Changes from previous version:
- Added message type handling (error / success)
- Fixed message color system (red for errors, green for success)
- Improved user feedback consistency
========================================

// The phone number is required but not unique, as multiple users
// (e.g., family members) may share the same contact number.
// The email field is used as the unique identifier for authentication.
// An NHS Number field was included as a unique identifier for each patient,
// reflecting real-world healthcare systems.
// While authentication is handled via email for simplicity,
// the NHS Number ensures accurate identification within the system.
*/

// Start session so the navigation can react to login status
session_start();

// Include the database connection
include 'db_connection.php';

// Message variables
$message = "";
$message_type = "";

// Check whether the registration form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Read and clean form input
    $nhs_number = trim($_POST['nhs_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];

    // Check all required fields
    if (
        !empty($nhs_number) &&
        !empty($first_name) &&
        !empty($last_name) &&
        !empty($email) &&
        !empty($password) &&
        !empty($phone)
    ) {

        // Validate NHS Number
        if (!preg_match('/^\d{10}$/', $nhs_number)) {
            $message = "NHS Number must contain exactly 10 digits.";
            $message_type = "error";

        } else {

            // Check duplicates
            $check_sql = "SELECT user_id FROM users WHERE email = ? OR nhs_number = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $email, $nhs_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Email or NHS Number is already registered.";
                $message_type = "error";

            } else {

                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $insert_sql = "INSERT INTO users (nhs_number, first_name, last_name, email, password_hash, phone, date_of_birth)
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param(
                    "sssssss",
                    $nhs_number,
                    $first_name,
                    $last_name,
                    $email,
                    $password_hash,
                    $phone,
                    $date_of_birth
                );

                if ($stmt->execute()) {
                    $message = "Registration successful. You can now sign in to your account.";
                    $message_type = "success";
                } else {
                    $message = "Error: could not register user.";
                    $message_type = "error";
                }
            }
        }

    } else {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        <div class="page-card">
            <h2 class="page-title">Create a New Account</h2>

            <p class="page-intro">
                Complete the form below to register as a new patient user in the system.
            </p>

            <!-- ✅ FIXED MESSAGE BLOCK -->
            <?php
            if (!empty($message)) {
                $class = "message";

                if ($message_type === "error") {
                    $class .= " message-error";
                } elseif ($message_type === "success") {
                    $class .= " message-success";
                } else {
                    $class .= " message-info";
                }

                echo "<div class='$class'>" . htmlspecialchars($message) . "</div>";
            }
            ?>

            <form method="POST">
                <div class="form-grid">

                    <div class="form-group">
                        <label>NHS Number</label>
                        <input type="text" name="nhs_number" maxlength="10" required>
                    </div>

                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth">
                    </div>

                </div>

                <div class="button-row">
                    <button class="btn-primary">Register</button>
                    <a href="index.php" class="btn-secondary">Back to Home</a>
                </div>
            </form>

            <div class="info-links">
                <p>Already have an account? <a href="login.php">Go to Login</a></p>
            </div>

        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
    </footer>

</body>
</html>