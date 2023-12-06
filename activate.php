<?php # 
// This page is for activating a newly registered CUSTOMER

$page_title = 'Activate Your Account';
include('includes/header.php');

// if x and y don't exist, redirect CUSTOMER
if (isset($_GET['x'], $_GET['y'])
	&& filter_var($_GET['x'], FILTER_VALIDATE_EMAIL)
	&& (strlen($_GET['y']) == 32 ) ) {
		
	// update the database
	require_once('mysqli_connect.php'); 
	$q = "UPDATE customers 
		SET active=NULL 
		WHERE (email='" . mysqli_real_escape_string($dbc, $_GET['x']) . "' 
			AND active='" . mysqli_real_escape_string($dbc, $_GET['y']) . "') 
		LIMIT 1";
	$r = mysqli_query($dbc, $q) or die("Query: $q\n<br>MySQL Error: " . mysqli_error($dbc));
	
	// print customized message
	if (mysqli_affected_rows($dbc) == 1) {
		echo "<h3>Your account is now active. You may now log in.</h3>";
	}
	else {
		echo '<p class="error">Your account could not be activated. Please re-check the link or contact the system administrator.</p>';
	}
	
	mysqli_close($dbc);
	
}  else { // redirect
	$url = 'http://localhost/sdev2521ch14/M5Competency/index.php';  
	header("Location: $url");
	exit();
}  // end of main if 
		
include('includes/footer.html');
?>
	