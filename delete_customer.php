<?php # Script 10.2 - delete_customer.php
// This page is for deleting a customer record.
// This page is accessed through view_customers.php

session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

$page_title = 'Delete a Customer';
include('includes/header.php');
echo '<h1>Delete a Customer</h1>';

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
    
    if($_POST['sure'] == 'Yes') { // Delete the record.

        // Make the query:
        $q = "DELETE FROM customers WHERE customer_id=$id LIMIT 1";
        $r = @mysqli_query($dbc, $q);
        if(mysqli_affected_rows($dbc) == 1) { // If it ran okay.

            // Print a message
            echo '<p>The customer has been deleted.</p>';

        } else { // If the query did not run okay.
            echo '<p class="error">The customer could not be deleted due to a system error.</p>'; // Public message
            echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $q . '</p>'; // Debugging message.
        }

    } else { // No confirmation of deletion.
        echo '<p>The customer has NOT been deleted.</p>';
    }

} else { // Show the form
    // Retrieve the customer's information.
    $q = "SELECT CONCAT(last_name, ', ', first_name) FROM customers WHERE customer_id=$id";
    $r = @mysqli_query($dbc, $q);

    if(mysqli_num_rows($r) == 1) { // Valid customer ID, show the form.

        // Get the customer's information:
        $row = mysqli_fetch_array($r, MYSQLI_NUM);

        // Display the record being deleted:
        echo "<h3>Name: $row[0]</h3>
        <p>Are you sure you want to delete this customer?</p>";

        // Create the form:
            echo '<form action="delete_customer.php" method="post">
                <input type="radio" name="sure" value="Yes"> Yes
                <input type="radio" name="sure" value="No" checked="checked"> No
                <input type="submit" name="submit" value="Submit">
                <input type="hidden" name="id" value="' . $id . '">
            </form>';

    } else { // Not a valid customer ID.
        echo '<p class="error">This page has been accessed in error.</p>';
    }

} // End of the main submission conditional.

mysqli_close($dbc);

include('includes/footer.html');
?>