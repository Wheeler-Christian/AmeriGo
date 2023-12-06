<?php # Script 10.3 - edit_customer.php
// This page is for editing a customer record.
// This page is accessed through view_customers.php

session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

$page_title = 'Edit a Customer';
include('includes/header.php');
echo '<h1>Edit a Customer</h1>';

// Check for a valid customer ID, through GET or POST:
if ( (isset($_GET['id'])) && (is_numeric($_GET['id'])) ) { // From view_customers.php
    $id = $_GET['id'];
} elseif ( (isset($_POST['id'])) && (is_numeric($_POST['id'])) ) { // Form submission.
    $id = $_POST['id'];
} else { // No valid id, kill the script.
    echo '<p class="error">This page has been accessed in error.</p>';
    include('includes/footer.html');
    exit();
}

require_once('mysqli_connect.php');

// Check if the form has been submitted:
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = []; // Use an array to track errors.
    
    // Check for a first name:
    if(empty($_POST['first_name'])) {
        $errors[] = 'You forgot to enter your first name.';
    } else {
        $fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
    }
    
    // Check for a last name:
    if(empty($_POST['last_name'])) {
        $errors[] = 'You forgot to enter your last name.';
    } else {
        $ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
    }
    
    // Check for an email address:
    if(empty($_POST['email'])) {
        $errors[] = 'You forgot to enter your email address.';
    } else {
        $em = mysqli_real_escape_string($dbc, trim($_POST['email']));
    }

    // Check for a phone number:
    if(empty($_POST['phone'])) {
        $errors[] = 'You forgot to enter your phone number.';
    } else {
        $ph = trim($_POST['phone']);
    }

    // Check for a birthdate:
    if(empty($_POST['dob'])) {
        $errors[] = 'You forgot to enter your birthday.';
    } else {
        $dob = trim($_POST['dob']);
    }

    // Check for a user level:
    if(empty($_POST['user_level'])) {
        $errors[] = 'You forgot to enter your user level.';
    } else {
        $ul = mysqli_real_escape_string($dbc, trim($_POST['user_level']));
        if($ul != 1 && $ul != 2) {
            $errors[] = 'User level must be a 1 or a 2.';
        }
    }    

    if(empty($errors)) { // If everything is okay.
        // Test for unique email address
        $q = "SELECT customer_id FROM customers WHERE email = '$em' AND customer_id != $id";
        $r = @mysqli_query($dbc, $q);
        if(mysqli_num_rows($r) == 0) {
            // Make the query:
            $q = "UPDATE customers
                SET first_name = '$fn', 
                    last_name = '$ln', 
                    email = '$em',
                    phone = '$ph',
                    dob = '$dob'
                    user_level = $ul
                WHERE customer_id = $id
                LIMIT 1";
            $r = @mysqli_query($dbc, $q);
            if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
                echo '<p>The customer has been edited.</p>';
            } else {  // no rows were affected              
                echo '<p>No records were updated.</p>';
            }
        } else { // Already registered.
            echo '<p class="error">The email address has already been registered.</p>';
        }
    } else { // Report the errors.
        echo '<p class="error">The following error(s) occurred:<br>';
        foreach($errors as $msg){//Print each error.
            echo " - $msg<br>\n";
        }
        echo '</p><p>Please try again.</p>';
    } //End of if(empty($errors)) IF.

} // End of the main submission conditional.

// Always show the form...

// Retrieve the customer's information:
$q = "SELECT first_name, last_name, email, phone, dob, user_level
    FROM customers 
    WHERE customer_id=$id";
$r = @mysqli_query($dbc, $q);

if(mysqli_num_rows($r) == 1) { // Valid customer ID, show the form.
    // Get the customer's information:
    $row = mysqli_fetch_array($r, MYSQLI_NUM);

    // First name:
    if(isset($_POST['first_name'])) { // checking for the $_POST variables for first name
        $fn = mysqli_real_escape_string($dbc, trim($_POST['first_name'])); // and using those values if they exist
    } else {
        $fn = $row[0]; // or the database values if they do not exist
    }

    // Last name:
    if(isset($_POST['last_name'])) { // checking for the $_POST variables for last name
        $ln = mysqli_real_escape_string($dbc, trim($_POST['last_name'])); // and using those values if they exist
    } else {
        $ln = $row[1]; // or the database values if they do not exist
    }

    // Email:
    if(isset($_POST['email'])) { // checking for the $_POST variables for email
        $em = mysqli_real_escape_string($dbc, trim($_POST['email'])); // and using those values if they exist
    } else {
        $em = $row[2]; // or the database values if they do not exist
    }

    // Phone:
    if(isset($_POST['phone'])) { // checking for the $_POST variables for phone
        $ph = trim($_POST['phone']); // and using those values if they exist
    } else {
        $ph = $row[3]; // or the database values if they do not exist
    }

    // Birthday:
    if(isset($_POST['dob'])) { // checking for the $_POST variables for date of birth
        $dob = trim($_POST['dob']); // and using those values if they exist
    } else {
        $dob = $row[4]; // or the database values if they do not exist
    }

    // user level:
    if(isset($_POST['user_level'])) { // checking for the $_POST variables for user level
        $ul = mysqli_real_escape_string($dbc, trim($_POST['user_level'])); // and using those values if they exist
    } else {
        $ul = $row[5]; // or the database values if they do not exist
    }    

    //Create the form:
    echo '<form action="edit_customer.php" method="post">
    <p>First Name: <input type="text" name="first_name" size="15" maxlength="15" value="' . $fn . '"></p>
    <p>Last Name: <input type="text" name="last_name" size="15" maxlength="30" value="' . $ln . '"></p>
    <p>Email Address: <input type="email" name="email" size="20" maxlength="60" value="' . $em . '"></p>
    <p>Phone Number: <input type="text" name="phone" size="20" maxlength="40" value="' . $ph . '"></p>
    <p>Birthday: <input type="text" name="dob" size="10" maxlength="10" value="' . $dob . '"></p>
    <p>User Level: <input type="text" name="user_level" size="1" value="' . $ul . '"></p> 
    <p><input type="submit" name="submit" value="Submit"></p>
    <input type="hidden" name="id" value="' . $id . '">
    </form>';

} else { // Not a valid customer ID.
    echo '<p class="error">This page has been accessed in error.</p>';
} // End of if(mysqli_num_rows($r) == 1) IF.

mysqli_close($dbc);

include('includes/footer.html');
?>
