<?php # Script 10.4 - view_customers.php with pagination
	// This script retrieves all the records from the customers table
	// and paginates the results.

	session_start(); // Start the session.

	// If no session variable exists, redirect the user:
	if(!isset($_SESSION['customer_id'])) {
    	// Need the functions:
   		require('includes/login_functions.inc.php');
   		redirect_user();
	}

	// add the page title, the header, the h1 tag
	$page_title = 'View Customers';
	include('includes/header.php');
	echo '<h1>Registered Customers</h1>';

	// the IF test for user_level -- IF the user_level is NOT 2, then display the error message "Access denied, this function needs a sys-admin",
	if($_SESSION['user_level'] != 2) {
		echo '<p>Access denied, this function needs a sys-admin.</p>';
	} else {
		// ELSE do all the rest of the script. This IF test should end just before the inclusion of the footer file. 
		require_once('mysqli_connect.php'); // Connect to the db

		// Number of records to show per page:
		$display = 15;
	
		// Determine how many pages there are...
		if(isset($_GET['p']) && is_numeric($_GET['p'])) { // Already been determined
			$pages = $_GET['p'];
		} else { // Need to determine.
			// Count the number of records:
			$q = "SELECT COUNT(customer_id) FROM customers";
			$r = @mysqli_query($dbc, $q);
			$row = @mysqli_fetch_array($r, MYSQLI_NUM);
			$records = $row[0];
	
			// Calculate the number of pages...
			if($records > $display) { // More than 1 page.
				$pages = ceil($records/$display);
			} else {
				$pages = 1;
			}
		} // End of p IF.
	
		// Determine where in the database to start returning results...
		if(isset($_GET['s']) && is_numeric($_GET['s'])) {
			$start = $_GET['s'];
		} else {
			$start = 0;
		}
	
		// Determine the sort...
		// Default is by id.
		$sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'id';
	
		// Determine the sorting order:
		switch($sort) {
			case 'ln':
				$order_by = 'last_name ASC';
				break;
			case 'fn':
				$order_by = 'first_name ASC';
				break;
			case 'em':
				$order_by = 'email ASC';
				break;
			case 'ph':
				$order_by = 'phone ASC';
				break;
			case 'dob':
				$order_by = 'dob ASC';
				break;			
			default:
				$order_by = 'customer_id ASC';
				$sort = 'id';
				break;	
		}
	
		// Define the query:
		$q = 
			"SELECT customer_id,
				last_name,
				first_name,
				email,
				phone,
				dob
			FROM customers 
			ORDER BY $order_by
			LIMIT $start, $display";
	
		$r = @mysqli_query($dbc, $q); // Run the query.
	
		// Table header
		echo '
		<table width="60%" class="left">
		<thead>
		<tr>
			<th>Edit</th>
			<th>Delete</th>
			<th><a href="view_customers.php?sort=ln">Last Name</a></th>
			<th><a href="view_customers.php?sort=fn">First Name</a></th>
			<th><a href="view_customers.php?sort=em">Email Address</a></th>
			<th><a href="view_customers.php?sort=ph">Phone Number</a></th>
			<th><a href="view_customers.php?sort=dob">Birthday</a></th>
		</tr>
		</thead>
		<tbody>
		';
		
		// Fetch and print all the records:
		$bg = '#eeeeee'; // Set the initial background color.
		while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
			$bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the backround color.
			echo '
			<tr bgcolor="' . $bg . '">
				<td><a href="edit_customer.php?id=' . $row['customer_id'] . '">Edit</a></td>
				<td><a href="delete_customer.php?id=' . $row['customer_id'] . '">Delete</a></td>
				<td>' . $row['last_name'] . '</td>
				<td>' . $row['first_name'] . '</td>
				<td>' . $row['email'] . '</td>
				<td>' . $row['phone'] . '</td>
				<td>' . $row['dob'] . '</td>
			</tr>
			';
		} // End of WHILE loop.
	
		echo '</tbody></table>'; // Close the table.
		mysqli_free_result ($r); // Free up the resources
		mysqli_close($dbc);
	
		// Make the links to other pages, if necessary.
		if($pages > 1) {
			// Add some spacing and start a paragraph:
			echo '<br><p>';
	
			//Determine what page the script is on:
			$current_page = ($start/$display) + 1;
			
			// If it's not the first page, make a Previous button:
			if($current_page != 1) {
				echo '<a href="view_customers.php?s=' . ($start - $display) . '&p=' . $pages . '&sort=' . $sort . '">Previous</a> ';
			}
	
			// Make the numeric links:
			for($i = 1; $i <= $pages; $i++) {
				if($i != $current_page) {
					echo '<a href="view_customers.php?s=' . ($display * ($i - 1)) . '&p=' . $pages . '&sort=' . $sort . '">' . $i . '</a> ';
				} else {
					echo $i . ' ';
				}
			} // End of FOR loop.
	
			// If it's not the last page, make a Next button:
			if($current_page != $pages) {
				echo '<a href="view_customers.php?s=' . ($start + $display) . '&p=' . $pages . '&sort=' . $sort . '">Next</a>';
			}
	
			echo '</p>'; // Close the paragraph.
		} // End of links section.		
	}

	include('includes/footer.html');
?>

