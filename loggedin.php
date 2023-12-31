<?php # Script 12.13 - loggedin.php
// The user is redirected here from login.php.

session_start(); // Start the session.

// If no session value is present, redirect the user:
// Also validate the HTTP_USER_AGENT!
if(!isset($_SESSION['agent']) OR ($_SESSION['agent'] != sha1($_SERVER['HTTP_USER_AGENT']) )) {
    // Need the functions:
    require('includes/login_functions.inc.php');
    redirect_user();
}

// Set the page title and include the HTML header:
$page_title = 'Logged In!';
include('includes/header.php');

// Print a customized message:
echo "<h1>Welcome to Transit Ticket Buyer, {$_SESSION['first_name']} {$_SESSION['last_name']}!</h1>
<p><a href=\"logout.php\">Logout</a></p>";

include('includes/footer.html');
?>