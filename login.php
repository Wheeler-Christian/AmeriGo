<?php # Script 12.12 - login.php
    // This page processes the login form submission.
    // This script now uses HTTP_USER_AGENT value for added security.

    // Check if the form has been submitted:
    if($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Need two helper files:
        require('includes/login_functions.inc.php');
        require('mysqli_connect.php');

        // Check the login:
        list($check, $data) = check_login($dbc, $_POST['email'], trim($_POST['pass']));

        if($check) { // okay!!

            // Start the session:
            session_start();

            // In the login.php script, the session variables are assigned from $data[]...
            $_SESSION['customer_id'] = $data['customer_id'];
            $_SESSION['first_name'] = $data['first_name'];
            $_SESSION['last_name'] = $data['last_name'];
            $_SESSION['user_level'] = $data['user_level'];

            // ...except for the email, which is assigned from $_POST.
            // All assignments from $data[] have to be completed before the assignment from $_POST.
            $_SESSION['email'] = $_POST['email'];

            // The customer might want to buy something
            $_SESSION['ticket_number'] = 0; // ticket number = 0 because the customer have not purchased anything yet:
            $_SESSION['cart'] = []; // Make sure the cart is empty
            
            // Store the HTTP_USER_AGENT:
            $_SESSION['agent'] = sha1($_SERVER['HTTP_USER_AGENT']);
                
            // Redirect:
            redirect_user('loggedin.php');

        } else { // Unsuccessful!
            // Assign $data to $errors for error reporting in the login_page.inc.php file.
            $errors = $data;
        } // End of if($check) IF.

        mysqli_close($dbc); // Close the database connection.
    
    } // End of the main submit conditional

    // Create the page:
    include('includes/login_page.inc.php');
?>