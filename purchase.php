<?php # Script 10.3 - purchase.php
// This page is for purchasing a ticket.
// This page is accessed through buy_tickets.php

// vvvvv page setup --------------------
session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

$page_title = 'Purchase a Ticket';
include('includes/header.php');
echo '<h1>Purchase a Ticket</h1>';
// ^^^^^ page setup --------------------

/**Function that makes Form 1 --------------------
 * 
 * This function returns a string like this example:
 * Segment ID: 1
 * Corridor: Green to BOI
 * From here: SLC
 * To here: OGD
 * Available: 100
 * Distance: 38.3 miles
 * Price: $3.83
 * 
 * After that, the quantity input and submit button vary by scenario
 * 
 * Scenario 1
 *  - This page was accessed from the buy_tickets.php page
 *  - We want to start a new ticket
 *  - Enable the quantity input and the submit button
 * Scenario 2
 *  - this page was accessed from Form 2 of purchase.php
 *  - We want to add the next segment on the ticket
 *  - DISABLE the quantity input but enable the submit button
 *  - Make a disclaimer that this will not work if insufficient quantity
 * Scenario 3
 *  - The POST attempt SUCCEEDED
 *  - The only purpose of the form here is to show them what they just added to the cart
 *  - DISABLE the quantity input and DISABLE the submit button
 */
function make_form1($scenario) {

    // vvvvv Make the quantity sticky ----------
    if($scenario == 1) {
        // Use the quantity from POST, if set
        if(isset($_POST['quantity'])) {
            $qty = $_POST['quantity'];
        } else {
            $qty = '';
        }
    } else { // Scenario 2 or 3        
        // Use the quantity stored in the $_SESSION variable
        $qty = $_SESSION['quantity'];    
    }
    // ^^^^^ Make the quantity sticky ----------

    // Scenario 1: autofocus on the quantity
    // Scenario 2 and 3: disable the quantity
    $attr1 = ($scenario == 1) ? ' autofocus' : ' disabled';

    // Scenario 2: add a disclaimer about the number of seats available
    $disclaimer = '';
    if($scenario == 2) {
        $disclaimer = '<p>If enough seats are available, this segment will be added to your ticket.</p>';
    }

    /* 
    Scenario 1 and 2
     - The header asks if they want to buy this segment
     - Show the submit button
    Scenario 3
     - The header indicates they successfully put the segment on their ticket
     - Do NOT show the submit button
    */
    $h3 = '';
    $submit_button = '';
    if($scenario == 3) {
        $h3 = '<h3>Success!</h3>';
    } else {
        $h3 = '<h3>Add this segment to your ticket?</h3>';
        $submit_button = '<p><input type="submit" name="submit" value="Add to Cart" autofocus></p>';
    }

    // This has the data relevant about the segment
    $row = $_SESSION['row_GET'];

    // Make the form
    $form1 = '<div>
    <form action="purchase.php" method="post">' .
        $h3 .
        '<p>Segment ID: ' . $row['segment_id'] . 
        '<br>Corridor: ' . $row['corridor'] . ' to ' . $row['eotl'] .
        '<br>From here: ' . $row['station1'] . ' (' . $row['city1'] . ')' .
        '<br>To here: ' . $row['station2'] . ' (' . $row['city2'] . ')' .
        '<br>Available: ' . $row['available_seats'] .
        '<br>Distance: ' . $row['distance'] . ' miles' .
        '<br>Price: $' . number_format($row['price'], 2) . '</p>
        <p>Quantity to buy: <input type="number" name="quantity" value="' . $qty . '"' . $attr1 . '></p>' .
        $disclaimer .    
        $submit_button .
    '</form>
    </div>';

    echo $form1;
}
// ^^^^^ Function that makes form1 --------------------

// Check for a valid segment ID, through GET or POST:
if ( (isset($_GET['segment_id'])) && (is_numeric($_GET['segment_id'])) ) { // From buy_tickets.php

    $segment_id = $_GET['segment_id'];

} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') { // Form submission.

    // We need segment_id to put in the cart
    $segment_id = $_SESSION['row_GET']['segment_id'];

} else { // No valid segment id, kill the script.

    echo '<p class="error">This page has been accessed in error!</p>';
    include('includes/footer.html');
    exit();

}

// After making sure we got a segment_id, connect to the database
require_once('mysqli_connect.php');

if($_SERVER['REQUEST_METHOD'] == 'GET') { // vvvvv Start of the main submission conditional. --------------------
    $success_POST = false; // request method was GET, so POST has not succeeded yet
    // vvvvv Query 2 gets relevant info about the reqeusted segment ----------
    $query_GET = "SELECT segment_id,
    	corridor,
        direction_id,
        eotl,
        station1,
        (SELECT CONCAT(city, ', ', state_id) FROM stations WHERE station_id = station1) AS city1,
        station2,
        (SELECT CONCAT(city, ', ', state_id) FROM stations WHERE station_id = station2) AS city2,    
        available_seats,
        distance
    FROM corridors
    INNER JOIN directions USING(corridor_id)
    INNER JOIN segments USING(direction_id)
    WHERE segment_id = $segment_id";

    $result_set_GET = @mysqli_query($dbc, $query_GET); // Run the query.

    // Make sure the query returned EXACTLY ONE row.
    if(mysqli_num_rows($result_set_GET) == 1) { // the query found EXACTLY ONE row for that segment id

        // Fetch that one row, store it in a variable, calculate the price, and store all of it in the session variable
        $row_GET = mysqli_fetch_array($result_set_GET, MYSQLI_ASSOC);
        $row_GET['price'] = round($row_GET['distance']/10, 2); // The price is 10 miles per dollar
        $_SESSION['row_GET'] = $row_GET; // Store it in a session variable so we don't have to fetch it again for POST.

    } else { // the query did not return 1 row which is bad. (THERE SHOULD BE ONE AND ONLY ONE RESULT FOR THAT SEGMENT ID)

        if(mysqli_num_rows($result_set_GET) > 1) {
            echo '<p class="error">There was an error. We could not find that segment.</p>'; // PUBLIC MESSAGE
            echo '<p class="error">There are multiple rows for segment_id = ' . $segment_id . ', which is bad.</p>'; // DEBUGGING MESSAGE
        } else {
            echo '<p class="error">This page has been accessed in error.</p>'; // PUBLIC MESSAGE
        }
        include('includes/footer.html');
        exit();
    }
    // ^^^^^ Query 2 gets relevant info about the reqeusted segment ----------
} else { // vvvvv $_SERVER['REQUEST_METHOD'] == 'POST' --------------------    
    
    $errors = []; // Use an array to track errors.

    if($_SESSION['new_ticket'] == true) {
        // This is the first segment on the ticket, so we need to determine the quantity
        // Validate the submitted quantity:
        if(empty($_POST['quantity'])) {
            $errors[] = 'You forgot to enter the quantity.';
        } else {
            $quantity = trim($_POST['quantity']);
            if(!is_numeric($quantity)) {
                $errors[] = 'The quantity must be a number.';
            }
            if(($quantity <= 0) || ($quantity != floor($quantity))) {
                $errors[] = 'The quantity must be a positive integer.';
            } else {
                // Query 1: This is the query we make when $_SERVER['REQUEST_METHOD'] == 'POST'
                $query_POST = "SELECT available_seats FROM segments WHERE segment_id = $segment_id";
                $result_set_POST = @mysqli_query($dbc, $query_POST);
                if(mysqli_num_rows($result_set_POST) == 1) { // Valid segment ID.
                    $row_POST = mysqli_fetch_array($result_set_POST, MYSQLI_NUM);
                    $available_seats = $row_POST[0];
                    if($quantity > $available_seats) {
                        $errors[] = 'There are not enough seats available.';
                    }
                } else { // Invalid segment ID, show errors.
                    echo '<p class="error">There was an error. We could not find that segment.</p>'; // PUBLIC MESSAGE
                    echo '<p class="error">There are multiple rows for segment_id = ' . $segment_id . ', which is bad.</p>'; // DEBUGGING MESSAGE                }
                }
            }
        }
    } // else not a new ticket, so we already know the quantity

    if(empty($errors)) { // POST was successful

        $success_POST = true; // POST was successful

        // When form is successfully submitted and validated, insert an entry into the cart:
        $_SESSION['row_GET']['ticket_number'] = $_SESSION['ticket_number']; // We need the ticket number
        if($_SESSION['new_ticket'] == true) {
            $_SESSION['quantity'] = $quantity; // Since this is a new ticket, we don't know the quantity
            $_SESSION['new_ticket'] = false;
        }
        $_SESSION['row_GET']['quantity'] = $_SESSION['quantity']; // We need the quantity
        $_SESSION['cart'][] = $_SESSION['row_GET']; // Insert that purchase_item in the cart
    
        // Inform the user of the success addition to the cart:
        echo '<p><strong>This purchase has been added to your shopping cart.</strong><br>
            <strong>Cart contains ' . count($_SESSION['cart']) . ' items.</strong></p>';
    
    } else { // Report the errors.
    
        $success_POST = false; // POST failed
    
        echo '<p class="error">The following error(s) occurred:<br>';
        foreach($errors as $msg){//Print each error.
            echo " - $msg<br>\n";
        }
        echo '</p><p>Please try again.</p>';
    
    } //End of if(empty($errors)) IF.
} // ^^^^^ End of the main submission conditional --------------------

// Always show form 1. Form 1 has 4 scenarios to consider.
// vvvvv Form 1 is for purchasing an individual segment ---------------------------------------------------------------------------------------------------------------------
if($_SERVER['REQUEST_METHOD'] == 'GET') {
    if($_SESSION['new_ticket'] == true) {
        make_form1(1); // Scenario 1    
    } else { // not a new ticket
        make_form1(2); // Scenario 2
    }
} else { // $_SERVER['REQUEST_METHOD'] == 'POST'
    if($success_POST) {
        make_form1(3); // Scenario 3
    } else {
        // Scenario 4: the POST attempt FAILED
        if($_SESSION['new_ticket'] == true) {
            // Scenario 4a: They never succeeded in Scenario 1.
            // Let them try Scenario 1 again.
            make_form1(1);
        } else {
            // Scenario 4b: They failed in Scenario 2.
            // Send them back to Scenario 2.
            make_form1(2);        
        }
    }
}
// ^^^^^ Form 1 is for purchasing the first segment of a ticket ---------------------------------------------------------------------------------------------------------------------

// vvvvv Form 2 is for suggesting the next segment for the ticket --------------------------------------------------------------------------------------------------------------
if($success_POST) {
    if($_SESSION['row_GET']['station2'] != $_SESSION['row_GET']['eotl']) {

        // Assign to variable for easier query
        $st2 = $_SESSION['row_GET']['station2'];

        // Query 3: get the segment that is downhill from the current one
        $query3 = 'SELECT segment_id
        FROM segments
        WHERE direction_id = ' . $_SESSION['row_GET']['direction_id']
            . "AND station1 = '$st2'";
        
        $result_set3 = @mysqli_query($dbc, $query3);

        if(mysqli_num_rows($result_set3) == 1) { // We found the id of the next segment.

            $row3 = mysqli_fetch_array($result_set3, MYSQLI_NUM);
            $next_gid = $row3[0]; // store the next segment's id.

            // vvvvv Start Form 2 ----------------            
            echo '<br>
                <form action="purchase.php" method="get">
                    <h3>Suggested Purchase</h3>
                    <p>Would you like to see the next segment on that same route?</p>
                    <p>If enough seats are available, that next segment would go on the same ticket.</p>
                    <p>It has to be the same quantity of seats as this ticket.</p>
                    <p><input type="submit" name="submit" value="See next segment?" autofocus></p>
                    <input type="hidden" name="segment_id" value="' . $next_gid .'">
                </form>';
            // ^^^^^ End of Form 2 ---------------

        } else {
            echo '<p>Could not find next segment due to a system error. We apologize for any inconvenience.</p>'; // public message
            echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
        }
    }
}
// ^^^^^ Form 2 is for suggesting the next segment for the ticket --------------------------------------------------------------------------------------------------------------

// vvvvv Form 3 is for checking out the whole cart ---------------------------------------------------------------------------------------------------------------------------
if(!empty($_SESSION['cart'])) {
    // If the cart is NOT empty, then we can show Form 3; else hide Form 3
    echo '<br>
    <form action="checkout.php">
        <h3>Checkout?</h3>
        <p>All done? Click here to check out.</p>
        <input type="submit" value="Check Out">
    </form>
    <br><br>';
}
// ^^^^^ Form 3 is for checking out the whole cart ---------------------------------------------------------------------------------------------------------------------------

mysqli_close($dbc);

include('includes/footer.html');
?>
