<?php # index.php

    session_start(); // Start the session.

    // Make the header at the top
    $page_title = 'Home';
    include('includes/header.php');

    echo '<div class="page-header"><h1>Welcome to Transit Ticket Buyer!</h1></div>

    <br>
    <h2>Ride Our Trains!</h2>
    <p>Our company seeks to help you get from one place to another without the 
    hassle of driving there. You can do other things while traveling! You
    can read a book, play games, work on that term paper...the opportunities
    are endless. Are high speed Wi-Fi allows you to listen to music, surf the
    web, and do research. Maybe not watch a movie though...our Wi-Fi is not
    quite equipped to handle every passenger to stream a movie at the same 
    time.</p>

    <p>We think that the benefits of mass transit are immense. Not everyone 
    has the money to buy a car, and not everyone is able to drive. Mass transit 
    reduces traffic collisions by consolidating many passengers into one vehicle 
    with one driver. It reduces traffic jams because of the same consolidation. 
    It reduces pollution by using less fuel. Thank you for supporting our vision
    for a more sustainable society!</p>

    <p>This website lets you buy a ticket for mass transit. Right now you can buy 
    train ticket in the Utah area. Our current goal is to link Salt Lake City 
    with the big cities close to it: Las Vegas, Boise, Reno, and Denver. Our 
    trains stop for about five minutes at each station to make sure everyone 
    has time to get on and off. Enjoy your ride!</p>';

	// add the page title, the header, the h1 tag
	echo '<br>
    <h2>Station Codes</h2>
    <p>Like airports, we give each of the stations a three-letter code that 
        uniquely identifies them. This makes the "Buy Tickets" page cleaner and
        easier to read. Here is a list of all our stations, with their associated
        three letter code.
    </p>
    <h3>Table of Station Codes</h3>';

	require_once('mysqli_connect.php'); // Connect to the db
	
	// Determine the sort...
	// Default is by segment id.
	$sort = (isset($_GET['sort'])) ? $_GET['sort'] : 'gid';
	
	// Determine the sorting order:
	switch($sort) {
		case 'sid':
			$order_by = 'stations.station_id ASC';
			break;
		case 'city':
			$order_by = 'stations.city ASC';
			break;
		case 'state':
			$order_by = 'states.state_name ASC';
			break;
		default:
			$order_by = 'stations.station_id ASC';
			$sort = 'sid';
			break;	
	}

    // This query selects all the stations, ordered and limited as requested
	$q = "SELECT stations.station_id,
	        stations.city,
            states.state_name
        FROM stations
        INNER JOIN states USING(state_id)
		ORDER BY $order_by";

	$r = @mysqli_query($dbc, $q); // Run the query.
	
	// Table header
	echo '
	<table width="30%" class="left">
	<thead>
	<tr>
		<th><a href="index.php?sort=sid">Station ID</a></th>
		<th><a href="index.php?sort=city">City</a></th>
		<th><a href="index.php?sort=state">State</a></th>
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
			<td>' . $row['station_id'] . '</td>
			<td>' . $row['city'] . '</td>
			<td>' . $row['state_name'] . '</td>
		</tr>
		';
	} // End of WHILE loop.

	echo '</tbody></table>'; // Close the table.
	mysqli_free_result ($r); // Free up the resources
	mysqli_close($dbc);    

    echo '<br>
    <h2>Regional Map of our Train Routes</h2>
    <div>
    <img src="./img/RegionalMap.jpg" alt="Regional Map">
    <p><em>This image was obtained from the
        <a href="https://utahrpa.org/latest-news/item/52-utah-rpa-sends-expression-of-interest-to-the-federal-railroad-administration">Utah Rail Passengers Association</a>
        which inspired this project.</em>
    </p>
    </div>';


    // Finish with the footer at the bottom
    include('includes/footer.html');
?>