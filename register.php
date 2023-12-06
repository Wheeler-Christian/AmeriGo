<?php # Script 9.5 - register.php #2
// This script performs an INSERT query to add a record to the customers table.

session_start(); // Start the session.

$page_title = 'Register';
include('includes/header.php');

// Check for form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	require('mysqli_connect.php'); // Connect to the db.

	$errors = []; // Initialize an error array.

	// The first name should be at least 2 and no more than 20 characters long; 
	// the last name should be at least 2 and no more than 40 characters long. 
	// Both can consist of uppercase and lowercase letters, spaces, and apostrophes.
	define('FIRST_NAME_PATTERN', '/^[A-Za-z \']{2,20}$/');
	define('LAST_NAME_PATTERN', '/^[A-Za-z \']{2,40}$/');
	
	// Check for a first name:
	if (empty($_POST['first_name'])) {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter your first name.';
	} else {
		// store the first name data in a variable
		$fn = mysqli_real_escape_string($dbc, trim($_POST['first_name']));
		// Does the first name match the regex pattern?
		if(!preg_match(FIRST_NAME_PATTERN, $fn)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'The first name does not match the required pattern for a first name.';
		}
	}

	// Check for a last name:
	if (empty($_POST['last_name'])) {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter your last name.';
	} else {
		// store the last name data in a variable
		$ln = mysqli_real_escape_string($dbc, trim($_POST['last_name']));
		// Does the first name match the regex pattern?
		if(!preg_match(LAST_NAME_PATTERN, $ln)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'The last name does not match the required pattern for a last name.';
		}
	}

	// Check for an email address:
	if (empty($_POST['email'])) {
		$errors[] = 'You forgot to enter your email address.';
	} else {
		// Define the email regex pattern as a constant:
		define('EMAIL_REGEX_PATTERN', '/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,6}$/');

		// Get the email:
		$e = mysqli_real_escape_string($dbc, trim($_POST['email']));

		// Test the email against the email regex pattern:
		if(preg_match(EMAIL_REGEX_PATTERN, $e)) {
			$q = "SELECT customer_id 
				FROM customers 
				WHERE email = '$e'"; // Is this email already in the database?
			$r = @mysqli_query($dbc, $q); // Run the query.
			$num_rows = mysqli_num_rows($r); // Count the rows
			if($num_rows != 0) { // If the query returned any results
				$errors[] = 'That email address already exists.'; // Tell the user that email is taken.
			}
		} else {
			$errors[] = 'Email is not correctly formatted.';
		}
	} // End of checking for email.

	// Check for a phone number:
	if (empty($_POST['phone'])) {
		$errors[] = 'You forgot to enter your phone number.';
	} else {
		$phone = mysqli_real_escape_string($dbc, trim($_POST['phone']));
	}

	//Get the birthdates formatted as YYYY-MM-DD from the select element:
	$dob = $_POST['birth_year'] . '-' . $_POST['birth_month'] . '-' . $_POST['birth_day'];

	// Check for a password and match against the confirmed password:
	if (!empty($_POST['pass1'])) {
		if ($_POST['pass1'] != $_POST['pass2']) {
			$errors[] = 'Your password did not match the confirmed password.';
		} else {
			$p = mysqli_real_escape_string($dbc, trim($_POST['pass1']));
		}
	} else {
		$errors[] = 'You forgot to enter your password.';
	}

	if (empty($errors)) { // If everything's OK.

		// Register the customer in the database...
		// In the code that runs if there are no errors (with first name, last name, email, both passwords match), you need to add the activation code. It is created with this code:
		$a = md5(uniqid(rand(), true));

		// Make the query:
		$q = "INSERT INTO 
			customers (first_name, last_name, email, phone, dob, user_level, active, pass, registration_date) 
			VALUES  ('$fn', '$ln', '$e', '$phone', '$dob', 1, '$a', SHA2('$p', 512), NOW() )";
		$r = @mysqli_query($dbc, $q); // Run the query.
		if ($r) { // If it ran OK.

			$url = 'http://localhost/sdev2521ch14/M5Competency/'; // setting up base of URL
			// send email to activate registration
			$body = "Thank you for registering at this site. To activate your account, please click on this link:\n\n";
			$body .= $url . 'activate.php?x=' . urlencode($e) . '&y=' . $a;
			mail('wheelerchristian33@gmail.com', 'Registration Confirmation', $body, 'From: admin@sitename.com');

			// Print a message:
			echo '<h1>Thank you for registering, ' . $fn . ' ' . stripslashes($ln) . '!</h1>
				<p>A confirmation email has been sent to your address. Please click on the 
				link in that email to activate your account.</p><p><br></p>';
			
			// Include the footer and quit the script:
			include('includes/footer.html');
			exit();

		} else { // If it did not run OK.

			// Public message:
			echo '<h1>System Error</h1>
			<p class="error">You could not be registered due to a system error. We apologize for any inconvenience.</p>';

			// Debugging message:
			echo '<p>' . mysqli_error($dbc) . '<br><br>Query: ' . $q . '</p>';

		} // End of if ($r) IF.

		mysqli_close($dbc); // Close the database connection.

		// Include the footer and quit the script:
		include('includes/footer.html');
		exit();

	} else { // Report the errors.

		echo '<h1>Error!</h1>
		<p class="error">The following error(s) occurred:<br>';
		foreach ($errors as $msg) { // Print each error.
			echo " - $msg<br>\n";
		}
		echo '</p><p>Please try again.</p><p><br></p>';

	} // End of if (empty($errors)) IF.

	mysqli_close($dbc); // Close the database connection.

} // End of the main Submit conditional.
?>
<h1>Register</h1>
<form action="register.php" method="post" novalidate>
	<p>First Name: <input type="text" name="first_name" size="15" maxlength="20" value="<?php if (isset($_POST['first_name'])) echo $_POST['first_name']; ?>"></p>
	<p>Last Name: <input type="text" name="last_name" size="15" maxlength="40" value="<?php if (isset($_POST['last_name'])) echo $_POST['last_name']; ?>"></p>
	<p>Email Address: <input type="email" name="email" size="20" maxlength="60" value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>"> </p>
	<p>Phone Number: <input type="text" name="phone" size="20" maxlength="40" value="<?php if (isset($_POST['phone'])) echo $_POST['phone']; ?>"></p>
	<p>Birthdate
		<?php # copied from Script 2.9 - calendar.php #2
        // This script makes 3 pull-down menus for an HTML form: months, days, years
    
        // Make the months array:
        $months = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Make the months pull-down menu:
        echo '<select name="birth_month">';
        foreach ($months as $key => $value) {
            echo "<option value=\"$key\">$value</option>\n";
        }
        echo '</select>';

        // Make the days pull-down menu:
        echo '<select name="birth_day">';
        for ($day = 1; $day <= 31; $day++) {
            echo "<option value=\"$day\">$day</option>\n";
        }
        echo '</select>';

        // Make the years pull-down menu:
        echo '<select name="birth_year">';
        for ($year = 1970; $year <= 2000; $year++) {
            echo "<option value=\"$year\">$year</option>\n";
        }
        echo '</select>';
        ?>
	</p>
	<p>Password: <input type="password" name="pass1" size="10" maxlength="20" value="<?php if (isset($_POST['pass1'])) echo $_POST['pass1']; ?>" ></p>
	<p>Confirm Password: <input type="password" name="pass2" size="10" maxlength="20" value="<?php if (isset($_POST['pass2'])) echo $_POST['pass2']; ?>" ></p>
	<p><input type="submit" name="submit" value="Register"></p>
</form>
<?php include('includes/footer.html'); ?>