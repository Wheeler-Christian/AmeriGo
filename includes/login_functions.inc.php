<?php # Script 12.2 - login_functions.inc.php
    // This page defines two functions used by the login/logout process.

    // This function determines an absolute URL and redirectsd the user there.
    function redirect_user($page = 'index.php') {

        // Start defining the URL:
        $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        // Remove any trailing slashes:
        $url = rtrim($url, '/\\');

        // Add the page:
        $url .= '/' . $page;

        // Redirect the user:
        header("Location: $url");
        exit(); // Quit the script.
    } // End of redirect_user() function.

    // This function validates the form data (the email and password).
    function check_login($dbc, $email = '', $pass = '') {

        $errors = []; // Initialize error array.

        // Validate the email address:
        if(empty($email)) {
            $errors[] = 'You forgot to enter your email address.';
        } else {
            // Define the email regex pattern as a constant:
		    define('EMAIL_REGEX_PATTERN', '/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,6}$/');
            
            // Get the email:
            $e = mysqli_real_escape_string($dbc, trim($email));

            // Test the email against the email regex pattern:
		    if(preg_match(EMAIL_REGEX_PATTERN, $e)) {
                // Is this email already in the database?
                $q = "SELECT customer_id
                    FROM customers 
                    WHERE email = '$e' AND active IS NULL";

			    $r = @mysqli_query($dbc, $q); // Run the query.
			    $num_rows = mysqli_num_rows($r); // Count the rows
			    if($num_rows == 0) { // If the query returned ZERO results
                    $errors[] = 'The email address and password entered do not match those on file, or your account is not activated.';
    			}
	    	} else {
		    	$errors[] = 'Email is not correctly formatted.';
		    }
        }

        // Validate the password:
        if(empty($pass)) {
            $errors[] = 'You forgot to enter your password.';
        } else {
            $p = mysqli_real_escape_string($dbc, trim($pass));
        }

        if(empty($errors)) { // If everything's okay.

            // Retrieve the customer_id, first_name, last_name, and user_level for that email/password combination:
            $q = "SELECT customer_id, first_name, last_name, user_level
                FROM customers 
                WHERE email='$e' AND pass=SHA2('$p', 512)";
            $r = @mysqli_query($dbc, $q); // Run the query.

            // Check the result of the query:
            if(mysqli_num_rows($r) == 1) {

                // Fetch the record:
                $row = mysqli_fetch_array($r, MYSQLI_ASSOC);

                // Return true and the record:
                return [true, $row];

            } else { // Not a match!
                $errors[] = 'The email address and password entered do not match those on file.';
            }

        } // End of empty($errors) IF.

        // Return false and the errors:
        return [false, $errors];

    } // End of check_login() function.