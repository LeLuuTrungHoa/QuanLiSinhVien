<?php
// Start the session to check for existing user data
session_start();

// Check if the user is already logged in
if (isset($_SESSION["user_id"])) {
    // If logged in, redirect to their respective dashboard based on role
    if (isset($_SESSION["role"])) {
        switch ($_SESSION["role"]) {
            case 'admin':
                header("location: admin/index.php");
                break;
            case 'lecturer':
                header("location: lecturer/index.php");
                break;
            case 'student':
                header("location: student/index.php");
                break;
            default:
                // If role is not set or invalid, redirect to login
                header("location: login.php");
                break;
        }
    } else {
        // If role is not in session, default to login
        header("location: login.php");
    }
} else {
    // If not logged in, redirect to the login page
    header("location: login.php");
}
exit; // Ensure no further code is executed after redirection
?>
