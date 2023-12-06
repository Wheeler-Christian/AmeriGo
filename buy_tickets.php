<?php # Script 10.4 - buy_tickets.php with pagination
	// This script retrieves all the records from the customers table
	// and paginates the results.

	session_start(); // Start the session.

	// If no session variable exists, redirect the user:
	if(!isset($_SESSION['customer_id'])) {
    	// Need the functions:
   		require('includes/login_functions.inc.php');
   		redirect_user();
	}

	// add the page title, the header, and the h1 tag
	if($_SESSION['user_level'] == 1) { // if the person logged in is a normal user

		$page_title = 'Buy Tickets';
		$_SESSION['new_ticket'] = true; // Everytime the customer views this page, they are buying a new ticket
		$_SESSION['ticket_number']++; // Move to the next ticket number
		$_SESSION['quantity'] = 0; // Since this is a new ticket, we don't know the quantity

	} else { // the person logged in is a sys-admin
		$page_title = 'View Segments';
	}
	include('includes/header.php'); // header
	echo "<h1>$page_title</h1>"; // h1 tag

	require_once('mysqli_connect.php'); // Connect to the db

	// Number of records to show per page:
	$display = 15;
	
	// Determine how many pages there are...
	if(isset($_GET['p']) && is_numeric($_GET['p'])) { // Already been determined
		$pages = $_GET['p'];
	} else { // Need to determine.
		// Count the number of records:
		$q = "SELECT COUNT(segment_id) FROM segments";
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
	// Default is by segment id.
	$sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'gid';
	
	// Determine the sorting order:
	switch($sort) {
		case 'gid':
			$order_by = 'segment_id ASC';
			break;
		case 'cor':
			$order_by = 'corridor ASC, eotl ASC, segment_id ASC';
			break;
		case 'st1':
			$order_by = 'station1 ASC';
			break;
		case 'st2':
			$order_by = 'station2 ASC';
			break;
		case 'avail':
			$order_by = 'available_seats ASC, segment_id ASC';
			break;
		case 'dis':
			$order_by = 'distance ASC';
			break;
		case 'pri': // price is just distance/10
			$order_by = 'distance ASC';
			break;
		default:
			$order_by = 'segment_id ASC';
			$sort = 'gid';
			break;	
	}

	$q = "SELECT segment_id,
		corridor,
		station1,
		station2,
	    eotl,
		available_seats,
		distance
	FROM corridors
	INNER JOIN directions USING(corridor_id)
	INNER JOIN segments USING(direction_id)
	ORDER BY $order_by
	LIMIT $start, $display";

	$r = @mysqli_query($dbc, $q); // Run the query.
	
	// Table header
	echo '<table width="70%" class="left">
	<thead>
	<tr>';
	if($_SESSION['user_level'] == 1) { // if the person logged in is a normal user
		echo '<th>Purchase</th>';	
	} else { // the person logged in is a sys-admin
		echo '<th>Edit</th>
			<th>Delete</th>';
	}
	echo '
		<th><a href="buy_tickets.php?sort=gid">Segment ID</a></th>
		<th><a href="buy_tickets.php?sort=cor">Corridor</a></th>
		<th><a href="buy_tickets.php?sort=st1">From Here</a></th>
		<th><a href="buy_tickets.php?sort=st2">To Here</a></th>
		<th><a href="buy_tickets.php?sort=avail">Available</a></th>
		<th><a href="buy_tickets.php?sort=dis">Distance</a></th>		
		<th><a href="buy_tickets.php?sort=pri">Price</a></th>		
	</tr>
	</thead>
	<tbody>
	';
	
	// Fetch and print all the records:
	$bg = '#eeeeee'; // Set the initial background color.
	while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
		$bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the backround color.
		echo '<tr bgcolor="' . $bg . '">';

		if($_SESSION['user_level'] == 1) { // if the person logged in is a normal user
			echo '<td><a href="purchase.php?segment_id=' . $row['segment_id'] . '">Purchase</a></td>';		
		} else { // the person logged in is a sys-admin
			echo '<td><a href="edit_segment.php?segment_id=' . $row['segment_id'] . '">Edit</a></td>';		
			echo '<td><a href="delete_segment.php?segment_id=' . $row['segment_id'] . '">Delete</a></td>';					
		}

		echo '<td>' . $row['segment_id'] . '</td>
			<td>' . $row['corridor'] . ' to '  . $row['eotl'] . '</td>
			<td>' . $row['station1'] . '</td>
			<td>' . $row['station2'] . '</td>
			<td>' . $row['available_seats'] . '</td>
			<td>' . $row['distance'] . '</td>
			<td>$' . number_format($row['distance']/10, 2) . '</td>
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
			echo '<a href="buy_tickets.php?s=' . ($start - $display) . '&p=' . $pages . '&sort=' . $sort . '">Previous</a> ';
		}

		// Make the numeric links:
		for($i = 1; $i <= $pages; $i++) {
			if($i != $current_page) {
				echo '<a href="buy_tickets.php?s=' . ($display * ($i - 1)) . '&p=' . $pages . '&sort=' . $sort . '">' . $i . '</a> ';
			} else {
				echo $i . ' ';
			}
		} // End of FOR loop.

		// If it's not the last page, make a Next button:
		if($current_page != $pages) {
			echo '<a href="buy_tickets.php?s=' . ($start + $display) . '&p=' . $pages . '&sort=' . $sort . '">Next</a>';
		}

		echo '</p>'; // Close the paragraph.
	} // End of links section.		

	if($_SESSION['user_level'] == 2) {
		echo '<br><br>
		<form action="add_segment.php" method="get">
		<input type="submit" value="Add New Segment">
		</form>';
	}

	include('includes/footer.html');
?>

