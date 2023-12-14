<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="css/sticky-footer-navbar.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header"><a class="navbar-brand" href="#">Transit Ticket Buyer</a></div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
					<?php
                        // Always display the link to the home page:
                        echo '<li';
                        if($page_title == 'Home') echo ' class="active"'; // is it active?
                        echo '><a href="index.php">Home</a></li>';

						// Display these links depending on whether the customer is logged in:
						if(isset($_SESSION['customer_id'])) { // If the customer is logged in:
                            if($_SESSION['user_level'] == 2) {
                                // Only administrators can access the "View Customers" page.
                                echo '<li'; 
                                if($page_title == 'View Customers') echo ' class="active"';
                                echo '><a href="view_customers.php">View Customers</a></li>';
                                
                                echo '<li'; 
                                if($page_title == 'View Segments') echo ' class="active"';
                                echo '><a href="buy_tickets.php">View Segments</a></li>';
                            } else {
                                // Regular customers can see the buy tickets and contact links
                                echo '<li'; 
                                if($page_title == 'Buy Tickets') echo ' class="active"';
                                echo '><a href="buy_tickets.php">Buy Tickets</a></li>';
                                
                                echo '<li'; 
                                if($page_title == 'Contact Form') echo ' class="active"';
                                echo '><a href="contact.php">Contact Us</a></li>';
                            }

                            // vvvvv Always show the purchase history and logout links
                            echo '<li'; 
                            if($page_title == 'Purchase History') echo ' class="active"';
                            echo '><a href="purchase_history.php">Purchase History</a></li>';
                            
                            echo '<li'; 
                            if($page_title == 'Logged In!') echo ' class="active"';
                            echo '><a href="logout.php">Logout</a></li>';
                            // ^^^^^ Always show the purchase history and logout links

                            // Dark mode toggler
                            // echo '<li><button type="button" onclick="toggle_dark()">'; 
                            // if($_SESSION['dark_mode']) {
                            //     echo 'DARK MODE';
                            // } else {
                            //     echo 'LIGHT MODE';
                            // }
                            // echo '</button></li>';

						} else { // Else the customer is not logged in:
                            echo '<li'; 
                            if($page_title == 'Register') echo ' class="active"';
                            echo '><a href="register.php">New Customer</a></li>';

							echo '<li';
                            if($page_title == 'Login' || $page_title == 'Logged Out!') echo ' class="active"';
                            echo'><a href="login.php">Login</a></li>';
						}
					?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <!-- Script 3.2 - header.html -->