<?php
/*
========================================
File: dashboard.php
Version: 2.0
Changes from previous version:
- Replaced full_name with first_name and last_name
- Updated welcome message to display full name using separate session values
- Added structured comments for group work reference
========================================
*/

// Start the session
session_start();

// Protect the page so only logged-in users can access it
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>
        Welcome,
        <?php
        // Display the logged-in user's full name safely
        echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
        ?>!
    </h2>

    <p>You are logged in.</p>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>