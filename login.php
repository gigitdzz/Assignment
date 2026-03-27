<?php
/*
========================================
File: login.php
Version: 3.1
Changes from previous version:
- Fixed message color system (error messages now use red styling)
- Added message type handling
- Improved security feedback
========================================
*/

session_start();
include 'db_connection.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {

        $sql = "SELECT user_id, first_name, last_name, email, password_hash FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];

                header("Location: dashboard.php");
                exit();
            }
        }

        // ERROR MESSAGE (RED)
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="top-bar">
        NHS Appointment Booking System
    </div>

    <header class="navbar">
        <div class="logo-section">
            <h1>NHS Booking</h1>
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

            <!-- MESSAGE FIX -->
            <?php
            if (!empty($message)) {
                $class = "message";

                if ($message_type === "error") {
                    $class .= " message-error";
                }

                echo "<div class='$class'>" . htmlspecialchars($message) . "</div>";
            }
            ?>

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
                    <button class="btn-primary">Login</button>
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