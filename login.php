<?php
/*
========================================
File: login.php
Version: 2.0
Changes from previous version:
- Replaced full_name with first_name and last_name
- Updated session variables to store first_name and last_name separately
- Kept generic login error message for better security
- Added structured comments for group work reference
========================================
*/

// Start the session to store user data after successful login
session_start();

// Include the database connection file
include 'db_connection.php';

// Variable used to display feedback messages
$message = "";

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and clean form input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check that required fields are not empty
    if (!empty($email) && !empty($password)) {

        // Prepare SQL query to find the user by email
        $sql = "SELECT user_id, first_name, last_name, email, password_hash FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        // Bind the email parameter to the prepared statement
        $stmt->bind_param("s", $email);

        // Execute the query
        $stmt->execute();

        // Get the query result
        $result = $stmt->get_result();

        // Check if exactly one user was found
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the entered password against the stored hashed password
            if (password_verify($password, $user['password_hash'])) {

                // Store user data in session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];

                // Redirect the user to the dashboard after successful login
                header("Location: dashboard.php");
                exit();
            }
        }

        // Use a generic error message for security
        $message = "Invalid email or password.";

    } else {
        // Show message if required fields are missing
        $message = "Please enter email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>User Login</h2>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST" action="">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p><a href="register.php">Create an account</a></p>
</body>
</html>