<?php
/*
========================================
File: login.php
Version: 4.0
Changes from previous version:
- Added role-based redirection (admin vs patient)
- Login uses email as unique identifier
- Stores role in session
- Improved message handling (error in red)
========================================
*/

// Start session to manage login state
session_start();

// Include database connection
include 'db_connection.php';

// Message variables
$message = "";
$message_type = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get user input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (!empty($email) && !empty($password)) {

        // Prepare query (secure against SQL injection)
        $sql = "SELECT user_id, first_name, last_name, email, password_hash, role 
                FROM users 
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {

                // Store user data in session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Role-based redirection
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }

                exit();
            }
        }

        // Generic error message (security best practice)
        $message = "Invalid email or password.";
        $message_type = "error";

    } else {
        $message = "Please enter email and password.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Shared CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-bar">
        NHS Appointment Booking System | Login
    </div>

    <header class="navbar">
        <div class="logo-section">
            <h1>NHS Booking</h1>
            <p>Secure access to your account</p>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="faq.php">FAQ</a>
            <a href="contact.php">Contact</a>
        </nav>
    </header>

    <main class="page-container">
        <div class="page-card">

            <h2 class="page-title">Sign In</h2>

            <p class="page-intro">
                Enter your registered email address and password to access your account securely.
            </p>

            <!-- MESSAGE DISPLAY -->
            <?php
            if (!empty($message)) {
                $class = "message";

                if ($message_type === "error") {
                    $class .= " message-error";
                }

                echo "<div class='$class'>" . htmlspecialchars($message) . "</div>";
            }
            ?>

            <!-- LOGIN FORM -->
            <form method="POST">

                <div class="form-group full-width">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group full-width">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <div class="button-row">
                    <button type="submit" class="btn-primary">Login</button>
                    <a href="index.php" class="btn-secondary">Back to Home</a>
                </div>

            </form>

            <div class="info-links">
                <p>Do not have an account yet? <a href="register.php">Create an account</a></p>
            </div>

        </div>
    </main>

</body>
</html>