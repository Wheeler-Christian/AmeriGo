<?php # Script 10.4 - purchase_history.php with pagination
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
	$page_title = 'Purchase History';
	include('includes/header.php');
	if($_SESSION['user_level'] == 2) { // sys-admin
		echo '<h1>Purchase History for All Customers</h1>';	
	} else { // if the person logged in is a normal user
		echo '<h1>Your Purchase History</h1>';	
	}

	require_once('mysqli_connect.php'); // Connect to the db

	// Number of records to show per page:
	$display = 15;
	
	// Determine how many pages there are...
	if(isset($_GET['p']) && is_numeric($_GET['p'])) { // Already been determined
		$pages = $_GET['p'];
	} else { // Need to determine.

		$where_clause = ''; // sys-admin sees everything
		if($_SESSION['user_level'] == 1) { // if the person logged in is a normal user
			// Normal user can only see their purchases
			$where_clause = ' WHERE customer_id = ' . $_SESSION['customer_id'];
		}

		// Count the number of records:
		$query = 
        'SELECT COUNT(*)
        FROM tickets
        INNER JOIN ticket_segments USING(ticket_id)' . 
		$where_clause;
        
		$r = @mysqli_query($dbc, $query);
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

	$where_clause = ''; // sys-admin sees everything
	if($_SESSION['user_level'] == 1) { // if the person logged in is a normal customer
		// Normal user can only see their purchases
		$where_clause = ' WHERE customer_id = ' . $_SESSION['customer_id'];
	}

	$query = 
    'SELECT *
    FROM tickets
    INNER JOIN ticket_segments USING(ticket_id)
    INNER JOIN segments USING(segment_id)
	INNER JOIN directions USING(direction_id)
    INNER JOIN corridors USING(corridor_id)
	INNER JOIN customers USING(customer_id)'
	. $where_clause . 
	" ORDER BY ticket_id DESC, segment_id ASC 
	LIMIT $start, $display";

	$r = mysqli_query($dbc, $query); // Run the query.
	
	// Table header
	if($_SESSION['user_level'] == 2) { // sys-admin?
		echo '<table width="100%" class="left">
		<thead>
		<tr>
			<th>Cust ID</th>
			<th>Customer Name</th>
			<th>Customer Email</th>';		
	} else { // regular customer
		echo '<table width="60%" class="left">
		<thead>
		<tr>';
	}

	echo '<th>Ticket ID</th>
		<th>Segment ID</th>
		<th>Corridor</th>
		<th>From Here</th>
		<th>To Here</th>
		<th>Price</th>
		<th>Quantity</th>
		<th>Total Price</th>
	</tr>
	</thead>
	<tbody>
	';
	
	// Fetch and print all the records:
	$bg = '#eeeeee'; // Set the initial background color.
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the backround color.
		echo '
		<tr bgcolor="' . $bg . '">';
		if($_SESSION['user_level'] == 2) {
			echo '<td>' . $row['customer_id'] . '</td>';
			echo '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
			echo '<td>' . $row['email'] . '</td>';			
		}
		echo '<td>' . $row['ticket_id'] . '</td>
			<td>' . $row['segment_id'] . '</td>
			<td>' . $row['corridor'] . ' to ' . $row['eotl'] . '</td>
			<td>' . $row['station1'] . '</td>
			<td>' . $row['station2'] . '</td>
			<td>$' . number_format($row['distance']/10, 2) . '</td>
			<td>' . $row['quantity'] . '</td>
			<td>$' . number_format($row['quantity'] * $row['distance']/10, 2) . '</td>
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
			echo '<a href="purchase_history.php?s=' . ($start - $display) . '&p=' . $pages . '">Previous</a> ';
		}

		// Make the numeric links:
		for($i = 1; $i <= $pages; $i++) {
			if($i != $current_page) {
				echo '<a href="purchase_history.php?s=' . ($display * ($i - 1)) . '&p=' . $pages . '">' . $i . '</a> ';
			} else {
				echo $i . ' ';
			}
		} // End of FOR loop.

		// If it's not the last page, make a Next button:
		if($current_page != $pages) {
			echo '<a href="purchase_history.php?s=' . ($start + $display) . '&p=' . $pages . '">Next</a>';
		}

		echo '</p>'; // Close the paragraph.
	} // End of links section.		

	include('includes/footer.html');
?>

