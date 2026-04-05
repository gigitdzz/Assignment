<?php
/*
========================================
File: register.php
Version: 4.3
Changes from previous version:
- Added live input formatting for NHS Number and Phone
- Added grouped form sections for better readability
- Sanitized formatted numeric inputs before validation and database insert
- Improved UX for long-number entry and checking
========================================

// The phone number is required but not unique, as multiple users
// (e.g., family members) may share the same contact number.
// The email field is used as the unique identifier for authentication.
// The NHS Number is used as a unique identifier for each patient.
// Admin users are NOT created via this form for security reasons.
*/

// ==============================
// SESSION INITIALIZATION
// ==============================
session_start();

// ==============================
// DATABASE CONNECTION
// ==============================
include 'db_connection.php';

// ==============================
// MESSAGE STATE
// ==============================
$message = "";
$message_type = "";

// ==============================
// FORM HANDLING
// ==============================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ------------------------------
    // RAW INPUT COLLECTION
    // These values may contain spaces added by frontend formatting
    // ------------------------------
    $nhs_number_raw = trim($_POST['nhs_number']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone_raw = trim($_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];

    // ------------------------------
    // INPUT SANITIZATION
    // Remove all non-digit characters from numeric fields
    // This allows formatted input like "123 456 7890"
    // while still validating/storing only digits
    // ------------------------------
    $nhs_number = preg_replace('/\D/', '', $nhs_number_raw);
    $phone = preg_replace('/\D/', '', $phone_raw);

    // ------------------------------
    // REQUIRED FIELD VALIDATION
    // ------------------------------
    if (
        !empty($nhs_number_raw) &&
        !empty($first_name) &&
        !empty($last_name) &&
        !empty($email) &&
        !empty($password) &&
        !empty($confirm_password) &&
        !empty($phone_raw)
    ) {

        // ------------------------------
        // NHS NUMBER VALIDATION
        // Must contain exactly 10 digits
        // ------------------------------
        if (!preg_match('/^[0-9]{10}$/', $nhs_number)) {
            $message = "NHS Number must contain exactly 10 digits.";
            $message_type = "error";

        // ------------------------------
        // PHONE NUMBER VALIDATION
        // Accept digits only after sanitization
        // Typical UK number length: 10 or 11 digits
        // ------------------------------
        } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
            $message = "Phone number must contain 10 or 11 digits.";
            $message_type = "error";

        // ------------------------------
        // PASSWORD CONFIRMATION CHECK
        // ------------------------------
        } elseif ($password !== $confirm_password) {
            $message = "Passwords do not match.";
            $message_type = "error";

        } else {

            // ------------------------------
            // DUPLICATE CHECK
            // Email and NHS Number must be unique
            // ------------------------------
            $check_sql = "SELECT user_id FROM users WHERE email = ? OR nhs_number = ?";
            $stmt = $conn->prepare($check_sql);
            $stmt->bind_param("ss", $email, $nhs_number);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $message = "Email or NHS Number is already registered.";
                $message_type = "error";

            } else {

                // ------------------------------
                // PASSWORD HASHING
                // ------------------------------
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // ------------------------------
                // INSERT PATIENT USER
                // Role defaults to 'patient'
                // ------------------------------
                $insert_sql = "INSERT INTO users
                    (nhs_number, first_name, last_name, email, password_hash, phone, date_of_birth)
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
                    $message = "Registration successful. You can now sign in.";
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

    <style>
        /* Page-specific grouping styles for better visual structure */
        .form-section {
            margin-top: 28px;
            padding: 22px;
            border: 1px solid #dbe4ee;
            border-radius: 12px;
            background-color: #f9fbfd;
        }

        .form-section-title {
            margin-bottom: 16px;
            color: #005eb8;
            font-size: 20px;
            font-weight: 700;
        }

        .form-hint {
            margin-top: 6px;
            font-size: 13px;
            color: #6b7280;
        }
    </style>
</head>
<body>

<!-- ==============================
TOP BAR
============================== -->
<div class="top-bar">
    NHS Appointment Booking System | Registration
</div>

<!-- ==============================
NAVIGATION
============================== -->
<header class="navbar">
    <div class="logo-section">
        <h1>NHS Booking</h1>
        <p>Secure patient registration system</p>
    </div>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="about.php">About Us</a>
        <a href="faq.php">FAQ</a>
        <a href="contact.php">Contact</a>
    </nav>
</header>

<!-- ==============================
MAIN CONTENT
============================== -->
<main class="page-container">
    <div class="page-card">

        <h2 class="page-title">Create a New Account</h2>

        <p class="page-intro">
            Complete the form below to register as a new patient user.
        </p>

        <!-- ==============================
        MESSAGE DISPLAY
        ============================== -->
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

        <!-- ==============================
        REGISTRATION FORM
        ============================== -->
        <form method="POST" novalidate>

            <!-- PERSONAL DETAILS -->
            <div class="form-section">
                <div class="form-section-title">Personal Details</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="date_of_birth">Date of Birth</label>
                        <input
                            type="date"
                            id="date_of_birth"
                            name="date_of_birth"
                            value="<?php echo isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : ''; ?>"
                        >
                    </div>
                </div>
            </div>

            <!-- IDENTIFICATION DETAILS -->
            <div class="form-section">
                <div class="form-section-title">Identification Details</div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nhs_number">NHS Number</label>
                        <input
                            type="text"
                            id="nhs_number"
                            name="nhs_number"
                            inputmode="numeric"
                            maxlength="12"
                            placeholder="123 456 7890"
                            value="<?php echo isset($_POST['nhs_number']) ? htmlspecialchars($_POST['nhs_number']) : ''; ?>"
                            required
                        >
                        <div class="form-hint">Format: 123 456 7890</div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            inputmode="numeric"
                            maxlength="12"
                            placeholder="07123 456789"
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                            required
                        >
                        <div class="form-hint">Example UK format: 07123 456789</div>
                    </div>
                </div>
            </div>

            <!-- ACCOUNT DETAILS -->
            <div class="form-section">
                <div class="form-section-title">Account Details</div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                        >
                    </div>

                    <div class="form-group full-width">
                        <label for="confirm_password">Confirm Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                        >
                    </div>
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="button-row">
                <button type="submit" class="btn-primary">Register</button>
                <a href="index.php" class="btn-secondary">Back to Home</a>
            </div>
        </form>

        <!-- LOGIN REDIRECT -->
        <div class="info-links">
            <p>Already have an account? <a href="login.php">Go to Login</a></p>
        </div>

    </div>
</main>

<!-- ==============================
FOOTER
============================== -->
<footer class="footer">
    <p>&copy; 2026 NHS Appointment Booking System | Student Project</p>
</footer>

<!-- ==============================
LIVE INPUT FORMATTING
============================== -->
<script>
    // --------------------------------
    // NHS NUMBER FORMATTER
    // Displays NHS number as: 123 456 7890
    // Stores only digits after PHP sanitization
    // --------------------------------
    const nhsInput = document.getElementById('nhs_number');

    nhsInput.addEventListener('input', function () {
        let digits = this.value.replace(/\D/g, '').slice(0, 10);

        let formatted = '';
        if (digits.length > 0) {
            formatted = digits.substring(0, 3);
        }
        if (digits.length > 3) {
            formatted += ' ' + digits.substring(3, 6);
        }
        if (digits.length > 6) {
            formatted += ' ' + digits.substring(6, 10);
        }

        this.value = formatted;
    });

    // --------------------------------
    // UK PHONE FORMATTER
    // Displays mobile-style format as: 07123 456789
    // Stores only digits after PHP sanitization
    // --------------------------------
    const phoneInput = document.getElementById('phone');

    phoneInput.addEventListener('input', function () {
        let digits = this.value.replace(/\D/g, '').slice(0, 11);

        let formatted = '';
        if (digits.length > 0) {
            formatted = digits.substring(0, 5);
        }
        if (digits.length > 5) {
            formatted += ' ' + digits.substring(5, 11);
        }

        this.value = formatted;
    });
</script>

</body>
</html>