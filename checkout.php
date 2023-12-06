<?php # Script - checkout.php
// This page is for checking out. It buys all the contents in the cart.
// This page is accessed through purchase.php.

// vvvvv First set up the page --------------------
session_start(); // Start the session.

// If no session variable exists, redirect the user:
if(!isset($_SESSION['customer_id'])) {
    // Need the functions:
   	require('includes/login_functions.inc.php');
   	redirect_user();
}

// Page title and include header file
$page_title = 'Check Out';
include('includes/header.php');
echo '<h1>Check Out</h1>';

// connect to the database
require_once('mysqli_connect.php');
// ^^^^^ First set up the page --------------------

/**Function to insert a new ticket into the database
 * Then get the id of that ticket we just created
 * Returns -1 if the first query failed
 * Returns -2 if the second query failed
 * Returns ticket_id if both succeeded
 */
function insert_into_tickets($dbc, $cid, $qty) {
    
    // vvvvv Query 1 ----------------------------------------
    // Since this is a new ticket, we need to create a new ticket in the database
    $query = 
    "INSERT INTO tickets		
    (customer_id, quantity, date_purchased)
    VALUES
    ($cid, $qty, NOW())";
            
    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
        echo '<p>Could not create the ticket due to a system error. We apologize for any inconvenience.</p>'; // public message
        echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
        return -1;
    }

    // TODO this keeps failing
    // vvvvv Query 2 ----------------------------------------
    // Get the ticket_id of the ticket just created (get the max ticket id)
    $query = 
    "SELECT MAX(ticket_id)
    FROM tickets";
    
    $result_set = @mysqli_query($dbc, $query); // Run the query.
    
    if(mysqli_num_rows($result_set) == 1) { // If it ran okay.
        $row = mysqli_fetch_array($result_set, MYSQLI_NUM); // Fetch the ticket_id
        return $row[0]; // Return the ticket_id
    } else { // Did not run okay -- end transaction
        echo '<p>Could not find the created ticket due to a system error. We apologize for any inconvenience.</p>'; // public message
        echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
        return -2;
    }
}
/** ^^^^^ Function to insert a new ticket into the database ---------------------------------------- */

/** vvvvv Function to check out using a transaction ------------------------------------------------------------ */
function start_transaction_checkout($dbc, $ticket_id, $gid, $qty) {

	// Start transaction -- turn autocommit off:
	mysqli_autocommit($dbc, false);

    // vvvvv Query 3 --------------------
    // Insert into the ticket_segments table the ticket_id and the segment_id
    $query = 
    "INSERT INTO ticket_segments		
    (ticket_id, segment_id)
    VALUES
    ($ticket_id, $gid)";

    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
        mysqli_rollback($dbc); // Rollback
        mysqli_autocommit($dbc, true); // end transaction
        display_error($dbc, $query, $gid);
        return false; // transaction failed
    }

    // vvvvv Query 4 --------------------
    // Update the segments table to decrease the available seats by the desired quantity
    $query = 
    "UPDATE segments
    SET available_seats = available_seats - $qty
    WHERE segment_id = $gid";

    $result_set = @mysqli_query($dbc, $query); // Run the query.

    if(mysqli_affected_rows($dbc) != 1) { // If it did *NOT* run okay -- end transaction
        mysqli_rollback($dbc); // Rollback
        mysqli_autocommit($dbc, true); // end transaction
        display_error($dbc, $query, $gid);
        return false; // transaction failed
    }

    // SUCCESS!
	mysqli_commit($dbc); // commit
	mysqli_autocommit($dbc, true); // end transaction
	return true;  // transaction succeeded
}
/** ^^^^^ Function to check out using a transaction ------------------------------------------------------------ */

/** vvvvv Function to display an error that happened during the transaction ---------------------------------------- */
function display_error($dbc, $query, $gid) {
    echo '<p class="error">Failed to check out the item with segment id = ' . $gid . '. We apologize for any inconvenience.</p>';	
	echo '<p>' . mysqli_error($dbc) . '<br>Query: ' . $query . '</p>'; // DEBUGGING MESSAGE
}
/** ^^^^^ Function to display an error that happened during the transaction ---------------------------------------- */

// vvvvv Calculate the grand total price --------------------
$grand_total_price = 0; // grand total starts at zero
foreach($_SESSION['cart'] as $purchase) {
    $grand_total_price += $purchase['price'] * $purchase['quantity'];
}
echo '<h3>Grand Total Price: $' . number_format($grand_total_price, 2) . '</h3>';
// ^^^^^ Calculate the grand total price --------------------

// vvvvv Print out a table with all the contents of the cart --------------------
// Table header
echo '
<table width="60%" class="left">
<thead>
<tr>
    <th>Ticket ID</th>
    <th>Segment</th>
    <th>Corridor</th>
    <th>From Here</th>
    <th>To Here</th>
    <th>Quantity</th>
    <th>Distance</th>		
    <th>Price</th>
    <th>Total</th>
</tr>
</thead>
<tbody>'
;

$ticket_number = 0; // Start at ticket number zero, because we haven't processed any tickets yet
$ticket_id = 0; // Ticket number is from "buy_tickets.php", while ticket id is from the database -- (We don't know the ticket id until created because ticket id is auto-increment)
$bg = '#eeeeee'; // Set the initial background color.

foreach($_SESSION['cart'] as $purchase) {
    // Have we entered into a new ticket?
    if($ticket_number != $purchase['ticket_number']) {

        // Update the ticket number to reflect new ticket
        $ticket_number = $purchase['ticket_number'];

        // Insert the new ticket into the database and get its id
        $ticket_id = insert_into_tickets($dbc, $_SESSION['customer_id'], $purchase['quantity']);
    }

    if($ticket_id > 0) {
        // vvvvv Update the database to reflect this purchase using a TRANSACTION
        $transaction_success = start_transaction_checkout($dbc, $ticket_id, $purchase['segment_id'], $purchase['quantity']);

        if($transaction_success) {
            // If this transaction succeeded, print out a row in the receipt.
            $bg = ($bg == '#eeeeee' ? '#ffffff' : '#eeeeee'); // Switch the backround color.
            echo '
            <tr bgcolor="' . $bg . '">
                <td>' . $ticket_id . '</td>
                <td>' . $purchase['segment_id'] . '</td>
                <td>' . $purchase['corridor'] . ' to ' . $purchase['eotl'] . '</td>
                <td>' . $purchase['station1'] . '</td>
                <td>' . $purchase['station2'] . '</td>
                <td>' . $purchase['quantity'] . '</td>
                <td>' . $purchase['distance'] . '</td>
                <td>$' . number_format($purchase['price'], 2) . '</td>
                <td>$' . number_format($purchase['price'] * $purchase['quantity'], 2) . '</td>
            </tr>';
        }
    } else {
        echo '<p>Failed to insert purchase with these attributes
        <br> - Customer ID: ' . $_SESSION['customer_id'] .
        '<br> - Segment ID: ' . $purchase['segment_id'] .
        '<br> - Quantity: ' . $purchase['quantity'] .
        '</p>'; // DEBUGGING MESSAGE
    }
    

} // end foreach purchase
echo '</tbody></table>'; // Close the table.
// ^^^^^ Print out a table with all the contents of the cart --------------------

// vvvvv End the page --------------------
$_SESSION['cart'] = []; // Clear the cart because they checked out.
mysqli_close($dbc);
include('includes/footer.html');
// vvvvv End the page --------------------
?>