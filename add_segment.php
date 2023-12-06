<?php # Script - add_segment.php 
// This script performs an INSERT query to add a record to the segments table.

session_start(); // Start the session.

$page_title = 'Add New Segment';
include('includes/header.php');

require('mysqli_connect.php'); // Connect to the db.

/** vvvvv Function to add an EXTERNAL OUTWARD segment using a transaction ==================================================================================================== */
function add_external_segment_outward($dbc, $dir_id, $eotl, $sta1, $sta2, $avl, $dist) {
	
	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

	// vvvvv Query: change eotl to be station2 -------------------------------------------
	$query = "UPDATE directions
	SET eotl = '$sta2'
	WHERE direction_id = $dir_id";

	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: get the NEW segment id -------------------------------------------
	// The NEW segment id is one more than the previous segment id's
	$query = "SELECT MAX(segment_id) FROM segments";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
		$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
		$new_gid = $row[0] + 1; // Store the new segment id
	} else { // Did not run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: insert the new segment -------------------------------------------
	// The NEW segment id is one more than the previous segment's id
	$query = "INSERT INTO segments
	(segment_id, direction_id, station1, station2, available_seats, distance)
	VALUES
	($new_gid, $dir_id, '$sta1', '$sta2', 100, $dist)";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to add an EXTERNAL OUTWARD segment using a transaction ==================================================================================================== */

/** vvvvv Function to add an EXTERNAL INWARD segment using a transaction ==================================================================================================== */
function add_external_segment_inward($dbc, $dir_id, $eotl, $sta1, $sta2, $avl, $dist) {
	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

	// vvvvv Query: get the NEW segment id -------------------------------------------
	// The NEW segment id is one more than the previous segment id's
	$query = "SELECT MAX(segment_id) FROM segments";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
		$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
		$new_gid = $row[0] + 1; // Store the new segment id
	} else { // Did not run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: insert the new segment -------------------------------------------
	// The NEW segment id is one more than the previous segment's id
	$query = "INSERT INTO segments
	(segment_id, direction_id, station1, station2, available_seats, distance)
	VALUES
	($new_gid, $dir_id, '$sta1', '$sta2', 100, $dist)";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to add an EXTERNAL INWARD segment using a transaction ==================================================================================================== */

/** vvvvv Function to add an INTERNAL segment using a transaction ==================================================================================================== 
 * In order to add a new segment, we must break an existing segment into two parts
*/
function add_internal_segment($dbc, $dir_id, $sta1, $sta2, $avl, $dist) {
	
	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

	// vvvvv Query: get the old segment id -------------------------------------------
	// The OLD segment id is based on the direction_id and the station1
	$query = "SELECT segment_id
	FROM segments
	INNER JOIN directions USING(direction_id)
	WHERE direction_id = $dir_id
		AND station1 = '$sta1'";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // if it ran okay
		$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
		$old_gid = $row[0]; // Store the new segment id
	} else {
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: get the NEW segment id -------------------------------------------
	// The NEW segment id is one more than the previous segment id's
	$query = "SELECT MAX(segment_id) FROM segments";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
		$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
		$new_gid = $row[0] + 1; // Store the new segment id
	} else { // Did not run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: insert the new segment -------------------------------------------
	// The NEW segment id is one more than the previous segment's id
	$query = "INSERT INTO segments
	(segment_id, direction_id, station1, station2, available_seats, distance)
	VALUES
	($new_gid, $dir_id, '$sta1', '$sta2', $avl, $dist)";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// vvvvv Query: update the old segment -------------------------------------------
	// The NEW segment id is one more than the previous segment's id
	$query = "UPDATE segments
	SET station1 = '$sta2',
		distance = distance - $dist
	WHERE segment_id = $old_gid";
	
	$result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		report_errors($dbc, $query);
		return false; // transaction failed
	}

	// SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to add an INTERNAL segment using a transaction ==================================================================================================== */



/** vvvvv Function to display an error that happened before the transaction ==================================================================================================== */
function report_input_errors(array $errors) {
	echo '<h1>Error!</h1>
	<p class="error">The following error(s) occurred:<br>';
	foreach ($errors as $msg) { // Print each error.
		echo " - $msg<br>\n";
	}
	echo '</p><p>Please try again.</p><p><br></p>';
}
/** ^^^^^ Function to display an error that happened before the transaction ==================================================================================================== */

/** vvvvv Function to display an error that happened during the transaction ==================================================================================================== */
function report_errors($dbc, $query) {
	echo '<p>Could not add segment due to a system error. We apologize for any inconvenience.</p>'; // public message
	echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
}
/** ^^^^^ Function to display an error that happened during the transaction ==================================================================================================== */

// Check for form submission:
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$errors = []; // Initialize an error array.

	// vvvvv Validate input vvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
	define('CORRIDOR_PATTERN', '/^[A-Za-z]{2,20}$/');
	define('STATION_ID_PATTERN', '/^[A-Z]{3}$/');
	
	// Check for a corridor:
	if (isset($_POST['corridor'])) {
		$corridor = mysqli_real_escape_string($dbc, trim($_POST['corridor']));
		if(!preg_match(CORRIDOR_PATTERN, $corridor)) {
			$errors[] = 'Corridor does not match the expected pattern.';
		} else {
			// Query: Get the corridor_id that goes with this corridor color
			$query = "SELECT corridor_id
			FROM corridors
			WHERE corridor = '$corridor'";

			$result_set = @mysqli_query($dbc, $query); // run the query

			if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
				$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
				$corridor_id = $row[0];
			} else {
				$errors[] = 'That corridor could not be found.';
			}
		}
	} else {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter the corridor.';
	}

	// Check for the end of line:
	if (isset($_POST['eotl'])) {
		$eotl = mysqli_real_escape_string($dbc, trim($_POST['eotl']));
		if(!preg_match(STATION_ID_PATTERN, $eotl)) {
			// the data is not a 3-letter station code
			$errors[] = 'Some possible choices for end of the line are SLC, BOI, DEN, LAS, and RNO. ';
		} else {

		}
	} else {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter the "end of line."';
	}

	// Check for station 1:
	if (isset($_POST['station1'])) {
		$station1 = mysqli_real_escape_string($dbc, trim($_POST['station1']));
		if(!preg_match(STATION_ID_PATTERN, $station1)) {
			// the data is not a 3-letter station code
			$errors[] = 'Station codes must be three uppercase letters, as shown on the home page.';
		} else {
			// Query: does that station exist in the database?
			$query = "SELECT station_ID
			FROM stations
			WHERE station_id = '$station1'";

			$result_set = @mysqli_query($dbc, $query); // Run the query.

			if(mysqli_num_rows($result_set) == 0) { // If station not found
				$errors[] = 'Station 1 could not be found.';
			}
		}
	} else {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter station 1.';
	}

	// Check for station 2:
	if (isset($_POST['station2'])) {
		$station2 = mysqli_real_escape_string($dbc, trim($_POST['station2']));
		if(!preg_match(STATION_ID_PATTERN, $station2)) {
			// the data is not a 3-letter station code
			$errors[] = 'Station codes must be three uppercase letters, as shown on the home page.';
		} else {
			// Query: does that station exist in the database?
			$query = "SELECT station_ID
			FROM stations
			WHERE station_id = '$station2'";

			$result_set = @mysqli_query($dbc, $query); // Run the query.

			if(mysqli_num_rows($result_set) == 0) { // If station not found
				$errors[] = 'Station 2 could not be found.';
			}
		}
	} else {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter station 2.';
	}

	// Check for available seats:
	if (empty($_POST['available_seats'])) {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter the available seats.';
	} else {
		// store the last name data in a variable
		$available_seats = mysqli_real_escape_string($dbc, trim($_POST['available_seats']));
		// Does the first name match the regex pattern?
		if(is_numeric($available_seats)) {
			if($available_seats < 0.1) {
				// the number is too low
				$errors[] = 'Available seats cannot be less than 0.1.';
			} elseif($available_seats > 100) {
				// the number is too high
				$errors[] = 'Available seats cannot be more than 100.';
			}
		} else {
			// the data is not a number
			$errors[] = 'Please enter a number for available seats.';
		}
	}
	// ^^^^^ Validate input ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

	// Check for distance:
	if (empty($_POST['distance'])) {
		// the data is empty so it displays an error message
		$errors[] = 'You forgot to enter the distance.';
	} else {
		// store the last name data in a variable
		$distance = mysqli_real_escape_string($dbc, trim($_POST['distance']));
		// Does the first name match the regex pattern?
		if(is_numeric($distance)) {
			if($distance < 0.1) {
				// the number is too low
				$errors[] = 'Distance cannot be less than 0.1.';
			} elseif($distance > 1000) {
				// the number is too high
				$errors[] = 'Distance cannot be more than 1000.';
			}
		} else {
			// the data is not a number
			$errors[] = 'Please enter a number for distance.';
		}
	}
	// ^^^^^ Validate input ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

	if (empty($errors)) { // If everything's OK.

		// vvvvv After getting the data, make sure the data makes sense together vvvvvvvvvvvvvvvvvvvv
				
		// vvvvv Cannot connect a station to itself vvvvv
		if($station1 == $station2) {
			// Are the two stations the same?
			$errors[] = 'You are not allowed to connect a station to itself.';
		}


		// vvvvv Is that corridor associated with that end of the line? vvvvv
		// Query: Get the direction_id of the route that has that corridor and that eotl
		$query = "SELECT direction_id
		FROM directions
		WHERE corridor_id = $corridor_id
			AND eotl = '$eotl'";
	
		$result_set = @mysqli_query($dbc, $query); // run the query

		if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
			$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
			$direction_id = $row[0]; // Store the direction_id
		} else {
			$errors[] = "We could not add that segment due to a system error. We apologize for any inconvenience."; // Public message
			$errors[] = "The $corridor corridor does not have $eotl as an end of the line."; // DEBUGGING MESSAGE
			$errors[] = 'Query: ' . $query; // DEBUGGING MESSAGE
		}

		// vvvvv Is this an internal segment, or external outward segment? vvvvv
		// Query: Find the eotl for the opposite direction on this same corridor
		$query = "SELECT eotl
		FROM directions
		WHERE corridor_id = '$corridor_id'
		AND eotl != '$eotl'";

		$result_set = mysqli_query($dbc, $query); // run the query

		if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
			$row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row.
			$opposite_eotl = $row[0];
		} else {
			$errors[] = 'Could not determine whether that was an internal or external segment.'; // Public message
			$errors[] = 'Query: ' . $query; // DEBUGGING MESSAGE
		}

		// vvvvv After getting the data, make sure the data makes sense together vvvvvvvvvvvvvvvvvvvv

		if(empty($errors)) {

			if($station1 == $eotl) {
				// They want to add an external segment facing outward
				// Register the segment in the database
				$bool = add_external_segment_outward($dbc, $direction_id, $eotl, $station1, $station2, $available_seats, $distance);
			} else {
				// If station2 is the eotl for the opposite eotl
				if($station2 == $opposite_eotl) {
					// They want to add an external segment facing inward
					// Register the segment in the database...
					$bool = add_external_segment_inward($dbc, $direction_id, $eotl, $station1, $station2, $available_seats, $distance);
				} else {
					// They want to add an internal segment
					// Register the segment in the database...
					$bool = add_internal_segment($dbc, $direction_id, $station1, $station2, $available_seats, $distance);
				}
			}

			if($bool) {
				echo '<h1>New Segment</h1>
				<p><strong>The new segment has been added.</strong></p>';
			} else {
				echo '<h1>New Segment</h1>
				<p class="error"><strong>The new segment has NOT been added.</strong></p>';			
			}

			// deprecated
			// mysqli_close($dbc); // Close the database connection.

			// // Include the footer and quit the script:
			// include('includes/footer.html');
			// exit();
		} else { // Report the input errors.
			report_input_errors($errors);
		}
	} else { // Report the input errors.
		report_input_errors($errors);
	} // End of if (empty($errors)) IF.

} // End of the main Submit conditional.
?>
<h1>New Segment</h1>
<form action="add_segment.php" method="post" novalidate>
    <p>Corridor: <input type="text" name="corridor" size="10" maxlength="20" value="<?php if(isset($_POST['corridor'])) echo $_POST['corridor']; ?>"></p>
    <p>Current end of the line: <input type="text" name="eotl" size="10" maxlength="20" value="<?php if(isset($_POST['eotl'])) echo $_POST['eotl']; ?>"></p>
    <p>Station 1: <input type="text" name="station1" size="10" maxlength="20" value="<?php if(isset($_POST['station1'])) echo $_POST['station1']; ?>"></p>
    <p>Station 2: <input type="text" name="station2" size="10" maxlength="20" value="<?php if(isset($_POST['station2'])) echo $_POST['station2']; ?>"></p>
	<p>Available Seats: <input type="number" name="available_seats" min="1" step="1" max="100" value="<?php if (isset($_POST['available_seats'])) echo $_POST['available_seats']; ?>"></p>	
	<p>Distance: <input type="number" name="distance" min="0.1" step="0.1" max="1000" value="<?php if (isset($_POST['distance'])) echo $_POST['distance']; ?>"></p>	
	<p><input type="submit" name="submit" value="Add Segment"></p>
</form>

<?php 
	mysqli_close($dbc); // Close the database connection.
	include('includes/footer.html'); // footer
?>