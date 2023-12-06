<?php # Script - delete_segment.php
// This page is for deleting a segment record.
// This page is accessed through buy_tickets.php

session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

$page_title = 'Delete a Segment';
include('includes/header.php');
echo '<h1>Delete a Segment</h1>';

// Check for a valid segment ID, through GET or POST:
if ( (isset($_GET['segment_id'])) && (is_numeric($_GET['segment_id'])) ) { // From buy_tickets.php
    $segment_id = $_GET['segment_id'];
} elseif ( (isset($_POST['segment_id'])) && (is_numeric($_POST['segment_id'])) ) { // Form submission.
    $segment_id = $_POST['segment_id'];
} else { // No valid segment_id, kill the script.
    echo '<p class="error">This page has been accessed in error.</p>';
    include('includes/footer.html');
    exit();
}

require_once('mysqli_connect.php');

/** vvvvv Function to delete a segment ============================================= */
function delete_segment($dbc, $gid) {

    // vvvvv Query 1 --------------------------------------
    // Get relevant information about the segment
    $query = "SELECT station1,
        station2,
        direction_id,
        corridor_id,
        eotl
    FROM segments
    INNER JOIN directions USING(direction_id)
    WHERE segment_id=$gid";

    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row that is returned.
        $station1 = $row[0]; // Store information in variables.
        $station2 = $row[1];
        $direction_id = $row[2];
        $corridor_id = $row[3];
        $eotl = $row[4];
    } else {
        display_error($dbc, $query);
        return false;
    }

    if($station2 == $eotl) {
        // It is an end segment facing OUTWARD
        return delete_end_segment_outward($dbc, $gid, $direction_id, $station1);
    }
    // else ...
    // vvvvv Query 2 --------------------------------------
    // Get the EOTL for the same corridor going the opposite direction
    $query = "SELECT eotl
    FROM directions
    WHERE corridor_id = $corridor_id
        AND direction_id != $direction_id";
    
    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row that is returned.
        $eotl_opposite = $row[0]; // Store information in variables.
    } else {
        display_error($dbc, $query);
        return false;
    }

    if($station1 == $eotl_opposite) {
        // It is an end segment facing INWARD
        return delete_end_segment_inward($dbc, $gid);
    } else {
        // It is an INTERNAL segment
        return delete_internal_segment($dbc, $gid, $station1, $station2, $direction_id);
    }
}
/** ^^^^^ Function to delete a segment ============================================= */

/** vvvvv Function to delete END segment facing OUTWARD using a transaction ==================================================================================================== */
function delete_end_segment_outward($dbc, $gid, $direction_id, $station1) {

   	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

    // vvvvv Query 1 ------------------------------------------------------------------------------------------
    // Update the eotl for this direction
    $query = 
    "UPDATE directions
    SET eotl = '$station1'
    WHERE direction_id = $direction_id";

    $result_set = @mysqli_query($dbc, $query); // run the query

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 1 ------------------------------------------------------------------------------------------

    // vvvvv Query 2 ------------------------------------------------------------------------------------------
    // Make the query to delete the requested segment
    $query = "DELETE FROM segments WHERE segment_id=$gid LIMIT 1";

    $result_set = @mysqli_query($dbc, $query);

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 2 ------------------------------------------------------------------------------------------

    // SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to delete END segment facing OUTWARD using a transaction ==================================================================================================== */

/** vvvvv Function to delete END segment facing INWARD using a transaction ==================================================================================================== */
function delete_end_segment_inward($dbc, $gid) {
    // vvvvv Query 1 ------------------------------------------------------------------------------------------
    // Make the query to delete the requested segment
    $query = "DELETE FROM segments WHERE segment_id=$gid LIMIT 1";

    $result_set = @mysqli_query($dbc, $query);

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	} else {
        return true;
    }
    // ^^^^^ Query 1 ------------------------------------------------------------------------------------------
}
/** ^^^^^ Function to delete END segment facing INWARD using a transaction ==================================================================================================== */

/** vvvvv Function to delete INTERNAL segment using a transaction ==================================================================================================== 
 * To properly delete a segment, we must update its downhill neighbor.
*/
function delete_internal_segment($dbc, $gid,  $old_station1, $old_station2, $direction_id) {

   	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

    // vvvvv Query 1 ------------------------------------------------------------------------------------------
    // Get information on the downhill neighbor segment
    $query = 
    "SELECT segment_id
    FROM segments
    WHERE station1 = '$old_station2'
    AND direction_id = $direction_id";

    $result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row that is returned.
		$neighbor_gid = $row[0]; // Store the neighbor segment's ID.
	} else { // Did not run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 1 ------------------------------------------------------------------------------------------

    // vvvvv Query 2 ------------------------------------------------------------------------------------------
    // Take the minimum of the available seats, and the sum of the distances
    $query = 
    "SELECT MIN(available_seats),
    SUM(distance)
    FROM segments
    WHERE segment_id IN ($gid, $neighbor_gid)";

    $result_set = @mysqli_query($dbc, $query); // run the query

	if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Get the row that is returned.
        $min_seat = $row[0]; // Assign the results to variables
        $sum_dist = $row[1];		
	} else { // Did not run okay -- end transaction
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 2 ------------------------------------------------------------------------------------------
    
    // vvvvv Query 3 ------------------------------------------------------------------------------------------
    // Update the downhill neighbor segment
    $query = 
    "UPDATE segments
    SET station1 = '$old_station1',
        available_seats = $min_seat,
        distance = $sum_dist
    WHERE segment_id = $neighbor_gid";

    $result_set = @mysqli_query($dbc, $query); // run the query

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 3 ------------------------------------------------------------------------------------------

    // vvvvv Query 4 ------------------------------------------------------------------------------------------
    // Make the query to delete the requested segment
    $query = "DELETE FROM segments WHERE segment_id=$gid LIMIT 1";

    $result_set = @mysqli_query($dbc, $query);

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
		mysqli_rollback($dbc); // Rollback
		mysqli_autocommit($dbc, true); // end transaction
		display_error($dbc, $query);
		return false; // transaction failed
	}
    // ^^^^^ Query 4 ------------------------------------------------------------------------------------------

    // SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to delete INTERNAL segment using a transaction ==================================================================================================== */

/** vvvvv Function to display an error that happened during the transaction ****************************************************************************************** */
function display_error($dbc, $query) {
	echo '<p class="error">Could not delete the segment due to a system error. We apologize for any inconvenience.</p>'; // public message
	echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
}
/** ^^^^^ Function to display an error that happened during the transaction ****************************************************************************************** */

// Check if the form has been submitted:
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if($_POST['sure'] == 'Yes') { // Delete the segment.
        $bool = delete_segment($dbc, $segment_id);
        if($bool) {
            echo '<p>The segment with id = ' . $segment_id . ' has been deleted.</p>';
        }
    } else { // No confirmation of deletion.
        echo '<p>The segment has NOT been deleted.</p>';
    }

} else { // GET method: Show the form
    // Retrieve the segment's information.
    $query = "SELECT segment_id,
        CONCAT(corridor, ' to ', eotl) AS corridor,
        station1,
        station2,
        available_seats,
        distance
    FROM corridors
    INNER JOIN directions USING(corridor_id)
    INNER JOIN segments USING(direction_id)
    WHERE segment_id=$segment_id";

    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_num_rows($result_set) == 1) { // Valid segment ID, show the form.

        // Get the segment's information:
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM);

        // Display the record being deleted:
        echo "<h3>Deleting the following:</h3>
        <p>Segment ID: $row[0]<br>
        Corridor: $row[1]<br>
        Station 1: $row[2]<br>
        Station 2: $row[3]<br>
        Available seats: $row[4]<br>
        Distance: $row[5]<br></p>
        <p>Are you sure you want to delete this segment?</p>";

        // Create the form:
        echo '<form action="delete_segment.php" method="post">
        <input type="radio" name="sure" value="Yes"> Yes
        <input type="radio" name="sure" value="No" checked="checked"> No
        <input type="submit" name="submit" value="Submit">
        <input type="hidden" name="segment_id" value="' . $segment_id . '">
        </form>';

    } else { // Not a valid segment ID.
        echo '<p class="error">This page has been accessed in error.</p>';
    }

} // End of the main submission conditional.

mysqli_close($dbc);

include('includes/footer.html');
?>