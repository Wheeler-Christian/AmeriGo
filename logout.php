<?php # Script 12.11 - logout.php
// This page lets the user logout.
// This version uses sessions.

session_start(); // Access the existing session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {

    // Need the functions:
    require('includes/login_functions.inc.php');
    redirect_user();

} else { // Cancel the session:

    $first_name = $_SESSION['first_name'];
    $_SESSION = []; // Clear the session variables.
    session_destroy(); // Destroy the session itself.
    setcookie('PHPSESSID', '', time()-3600, '/', '', 0, 0); // Destroy the session id cookie.

}

// Set the page title and include the html header:
$page_title = 'Logged Out!';
include('includes/header.php');

// Print a customized message:
echo "<h1>Goodbye, $first_name! Thanks for visiting!</h1>";
include('includes/footer.html');
?>