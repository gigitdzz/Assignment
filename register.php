<?php
/*
========================================
File: register.php
Version: 2.0
Changes from previous version:
- Replaced full_name with first_name and last_name
- Added nhs_number field
- Made phone field required
- Added duplicate checks for both email and NHS number
- Added permanent project notes for group reference
========================================

// The phone number is required but not unique, as multiple users
// (e.g., family members) may share the same contact number. The email
// field is used as the unique identifier for authentication.
// An NHS Number field was included as a unique identifier for each patient,
// reflecting real-world healthcare systems.
// While authentication is handled via email for simplicity,
// the NHS Number ensures accurate identification within the system.
*/

// Include the database connection file
include 'db_connection.php';

// Variable used to display feedback messages
$message = "";

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and clean form input
    $nhs_number = trim($_POST['nhs_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];

    // Check that all required fields are filled in
    if (
        !empty($nhs_number) &&
        !empty($first_name) &&
        !empty($last_name) &&
        !empty($email) &&
        !empty($password) &&
        !empty($phone)
    ) {

        // Optional validation: NHS Number should contain exactly 10 digits
        if (!preg_match('/^\d{10}$/', $nhs_number)) {
            $message = "NHS Number must contain exactly 10 digits.";
        } else {

            // Check whether the email or NHS number already exists
            $check_sql = "SELECT user_id FROM users WHERE email = ? OR nhs_number = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $email, $nhs_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Email or NHS Number is already registered.";
            } else {

                // Hash the password before storing it in the database
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the users table
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
                    $message = "Registration successful.";
                } else {
                    $message = "Error: could not register user.";
                }
            }
        }
    } else {
        $message = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>User Registration</h2>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST" action="">
        <label for="nhs_number">NHS Number:</label><br>
        <input type="text" id="nhs_number" name="nhs_number" maxlength="10" required><br><br>

        <label for="first_name">First Name:</label><br>
        <input type="text" id="first_name" name="first_name" required><br><br>

        <label for="last_name">Last Name:</label><br>
        <input type="text" id="last_name" name="last_name" required><br><br>

        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <label for="phone">Phone:</label><br>
        <input type="text" id="phone" name="phone" required><br><br>

        <label for="date_of_birth">Date of Birth:</label><br>
        <input type="date" id="date_of_birth" name="date_of_birth"><br><br>

        <button type="submit">Register</button>
    </form>

    <p><a href="login.php">Go to Login</a></p>
</body>
</html>