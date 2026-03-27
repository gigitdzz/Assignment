<?php
/*
========================================
File: logout.php
Version: 1.0
Changes from previous version:
- Added file header comments for group work reference
========================================
*/

// Start the session
session_start();

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit();
?>