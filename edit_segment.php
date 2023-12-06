<?php # Script 10.3 - edit_customer.php
// This page is for editing a segment record.
// This page is accessed through view_customers.php

session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

$page_title = 'Edit a Segment';
include('includes/header.php');
echo '<h1>Edit a Segment</h1>';

// Check for a valid segment ID, through GET or POST:
if ( (isset($_GET['segment_id'])) && (is_numeric($_GET['segment_id'])) ) { // From view_customers.php
    $segment_id = $_GET['segment_id'];
} elseif ( (isset($_POST['segment_id'])) && (is_numeric($_POST['segment_id'])) ) { // Form submission.
    $segment_id = $_POST['segment_id'];
} else { // No valid segment_id, kill the script.
    echo '<p class="error">This page has been accessed in error!</p>';
    include('includes/footer.html');
    exit();
}

require_once('mysqli_connect.php');

// Check if the form has been submitted:
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $errors = []; // Use an array to track errors.
    
    define('CORRIDOR_PATTERN', '/^[A-Za-z]{2,20}$/');
    define('STATION_ID_PATTERN', '/^[A-Z]{3}$/');

    // Check for a corridor:
    if(empty($_POST['corridor'])) {
        $errors[] = 'You forgot to enter the corridor.';
    } else {
        $cor = mysqli_real_escape_string($dbc, trim($_POST['corridor']));
        if(!preg_match(CORRIDOR_PATTERN, $cor)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'Corridor does not match the expected pattern.';
		} else {
            // Convert to lower case except first letter
            $cor = ucfirst(strtolower($cor));

            // Query 1: Make sure that corridor actually exists in the database
            $query = "SELECT corridor_id 
                FROM corridors 
                WHERE corridor = '$cor'";

            $result_set = @mysqli_query($dbc, $query); // Run the query.
			if(mysqli_num_rows($result_set) == 0) { // If the query did not find that corridor
				$errors[] = 'That corridor could not be found.';
			}
        }
    }
    
    // Check for End Of The Line:
    if(empty($_POST['eotl'])) {
        $errors[] = 'You forgot to enter the "End Of The Line."';
    } else {
        $eotl = mysqli_real_escape_string($dbc, trim($_POST['eotl']));
        if(!preg_match(STATION_ID_PATTERN, $eotl)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'End Of The Line does not match the expected 3-letter pattern.';
		} else {
            // Query 2: Make sure that eotl exists
            $query = "SELECT direction_id 
                FROM directions 
                WHERE eotl = '$eotl'";

            $result_set = @mysqli_query($dbc, $query); // Run the query.
			if(mysqli_num_rows($result_set) == 0) { // If the query did not find that eotl
				$errors[] = 'That End Of The Line could not be found.';
			}
        }
    }

    // Check for a station1:
    if(empty($_POST['station1'])) {
        $errors[] = 'You forgot to enter station 1.';
    } else {
        $st1 = mysqli_real_escape_string($dbc, trim($_POST['station1']));
        if(!preg_match(STATION_ID_PATTERN, $st1)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'Station 1 does not match the expected 3-letter pattern.';
		} else {
            // Query 3: Make sure that Station 1 exists
            $query = "SELECT station_id
                FROM stations
                WHERE station_id = '$st1'";

            $result_set = @mysqli_query($dbc, $query); // Run the query.
			if(mysqli_num_rows($result_set) == 0) { // If the query did not find that station
				$errors[] = 'Station 1 could not be found.';
			}
        }
    }

    // Check for a station2:
    if(empty($_POST['station2'])) {
        $errors[] = 'You forgot to enter station 2.';
    } else {
        $st2 = mysqli_real_escape_string($dbc, trim($_POST['station2']));
        if(!preg_match(STATION_ID_PATTERN, $st2)) {
			// the data does not match the regex and it displays an error message
			$errors[] = 'Station 2 does not match the expected 3-letter pattern.';
		} else {
            // Query 3: Make sure that Station 2 exists
            $query = "SELECT station_id
                FROM stations
                WHERE station_id = '$st2'";

            $result_set = @mysqli_query($dbc, $query); // Run the query.
			if(mysqli_num_rows($result_set) == 0) { // If the query did not find that station
				$errors[] = 'Station 2 could not be found.';
			}
        }
    }

    // Check for a available seats:
    if(empty($_POST['available_seats'])) {
        $errors[] = 'You forgot to enter the available seats.';
    } else {
        $avl = mysqli_real_escape_string($dbc, trim($_POST['available_seats']));
        if(!is_numeric($avl)) {
            $errors[] = 'Available seats must be a number';
        } elseif(($avl <= 0) || ($avl != floor($avl))) {
            $errors[] = 'Available seats must be a positive integer.';
        } elseif($avl > 100) {
            $errors[] = 'Available seats cannot be bigger than 100.';
        }
    }

    // Check for a distance:
    if(empty($_POST['distance'])) {
        $errors[] = 'You forgot to enter the distance.';
    } else {
        $dis = mysqli_real_escape_string($dbc, trim($_POST['distance']));
        if(!is_numeric($dis)) {
            $errors[] = 'Distance must be a number';
        } elseif($dis <= 0) {
            $errors[] = 'Distance must be a positive integer.';
        } elseif($dis > 1000) {
            $errors[] = 'Distance cannot be bigger than 1000.';
        }
    }    

    if(empty($errors)) { // If everything is okay.

        // Query 4: Find the direction_id that uniquely combines that corridor and that eotl
        $query = "SELECT direction_id 
            FROM directions 
            INNER JOIN corridors USING(corridor_id)
            WHERE corridor = '$cor'
            AND eotl = '$eotl'";

        $r = @mysqli_query($dbc, $query); // Run the query.
        if(mysqli_num_rows($r) != 0) { // That eotl is indeed associated with that corridor.

            $row = mysqli_fetch_array($r, MYSQLI_NUM); // Fetch the direction_id 
            $dir_id = $row[0];
            
            // Query 5: Make the UPDATE query:
            $query = "UPDATE segments
                SET direction_id = $dir_id, 
                    station1 = '$st1',
                    station2 = '$st2',
                    available_seats = $avl,
                    distance = $dis
                WHERE segment_id = $segment_id
                LIMIT 1";
            $r = @mysqli_query($dbc, $query);
            if (mysqli_affected_rows($dbc) == 1) { // If it ran okay.
                echo '<p>The segment has been edited.</p>';
            } else {  // no rows were affected              
                echo '<p>No records were updated.</p>'; // Public message.
                echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
            }
        } else {
            echo '<p class="error">That eotl is not associated with the ' . $cor . ' corridor.</p>';
        }
    } else { // Report the errors.
        echo '<p class="error">The following error(s) occurred:<br>';
        foreach($errors as $msg){//Print each error.
            echo " - $msg<br>\n";
        }
        echo '</p><p>Please try again.</p>';
    } //End of if(empty($errors)) IF.

} // End of the main submission conditional.

// Always show the form...

// Retrieve the segment's information:
$query = 'SELECT *
    FROM segments
    INNER JOIN directions USING(direction_id)
    INNER JOIN corridors USING(corridor_id)
    WHERE segment_id = ' . $segment_id;

$r = @mysqli_query($dbc, $query); // run the query

if(mysqli_num_rows($r) == 1) { // Valid segment ID, show the form.
    // Get the segment's information:
    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);

    // corridor:
    if(isset($_POST['corridor'])) { // checking for the $_POST variables for corridor
        $cor = mysqli_real_escape_string($dbc, trim($_POST['corridor'])); // and using those values if they exist
    } else {
        $cor = $row['corridor']; // or the database values if they do not exist
    }

    // eotl:
    if(isset($_POST['eotl'])) { // checking for the $_POST variables for eotl
        $eotl = mysqli_real_escape_string($dbc, trim($_POST['eotl'])); // and using those values if they exist
    } else {
        $eotl = $row['eotl']; // or the database values if they do not exist
    }

    // station1:
    if(isset($_POST['station1'])) { // checking for the $_POST variables for station1
        $st1 = mysqli_real_escape_string($dbc, trim($_POST['station1'])); // and using those values if they exist
    } else {
        $st1 = $row['station1']; // or the database values if they do not exist
    }

    // station2:
    if(isset($_POST['station2'])) { // checking for the $_POST variables for station2
        $st2 = trim($_POST['station2']); // and using those values if they exist
    } else {
        $st2 = $row['station2']; // or the database values if they do not exist
    }

    // available seats:
    if(isset($_POST['available_seats'])) { // checking for the $_POST variables for available seats
        $avl = trim($_POST['available_seats']); // and using those values if they exist
    } else {
        $avl = $row['available_seats']; // or the database values if they do not exist
    }

    // distance:
    if(isset($_POST['distance'])) { // checking for the $_POST variables for distance
        $dis = mysqli_real_escape_string($dbc, trim($_POST['distance'])); // and using those values if they exist
    } else {
        $dis = $row['distance']; // or the database values if they do not exist
    }    

    // Create the form:
    echo '<form action="edit_segment.php" method="post" novalidate>
    <p>Corridor: <input type="text" name="corridor" size="10" maxlength="20" value="' . $cor . '"></p>
    <p>End Of The Line: <input type="text" name="eotl" size="4" maxlength="10" value="' . $eotl . '"></p>
    <p>Station 1: <input type="text" name="station1" size="4" maxlength="10" value="' . $st1 . '"></p>
    <p>Station 2: <input type="text" name="station2" size="4" maxlength="10" value="' . $st2 . '"></p>
    <p>Available Seats: <input type="number" name="available_seats" min="1" max="100" value="' . $avl . '"></p>
	<p>Distance: <input type="number" name="distance" min="0.1" step="0.1" max="1000" value="' . $dis . '"></p>	
    <input type="hidden" name="segment_id" value="' . $segment_id . '">
    <input type="submit" name="submit" value="Submit">
    </form>';

} else { // Not a valid segment ID.
    echo '<p class="error">This page has been accessed in error.</p>';
} // End of if(mysqli_num_rows($r) == 1) IF.

mysqli_close($dbc);

include('includes/footer.html');
?>
