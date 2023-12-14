<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php echo $page_title; ?>
    </title>
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous"> -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> -->
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="css/sticky-footer-navbar.css" rel="stylesheet">
</head>

<body>
    <!-- ******************************** SANDBOX ******************************** -->

    <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
        <div class="container">
            <ul class="navbar-nav nav-tabs">

                <!-- PHP -->
                <?php
                function make_li_tag($current_pt, $next_pt, $filename) {
                    return '<li class="nav-item"> <a class='
                    . (($current_pt == $next_pt) ? ('"nav-link active" ') : ('"nav-link" '))
                    . 'href="' . $filename . '">' . $next_pt . '</a></li>';
                }

                // Always display the link to the home page:
                echo make_li_tag($page_title, 'Home', 'index.php');

                // Display these links depending on whether the customer is logged in:
                if(isset($_SESSION['customer_id'])) { // If the customer is logged in:
                    if($_SESSION['user_level'] == 2) {
                        // Administrators can access the "View Customers" and "View Segments" pages.
                        echo make_li_tag($page_title, 'View Customers', 'view_customers.php');
                        echo make_li_tag($page_title, 'View Segments', 'buy_tickets.php');
                                             
                    } else {
                        // Regular customers can see the buy tickets and contact links
                        echo make_li_tag($page_title, 'Buy Tickets', 'buy_tickets.php');
                        echo make_li_tag($page_title, 'Contact Us', 'contact.php');
                    }
                    // vvvvv Always show the purchase history and logout links
                    echo make_li_tag($page_title, 'Purchase History', 'purchase_history.php');
                    echo make_li_tag($page_title, 'Logout', 'logout.php');
                    // ^^^^^ Always show the purchase history and logout links
                    } else { // Else the customer is not logged in:
                        echo make_li_tag($page_title, 'New Customer', 'register.php');
                        echo make_li_tag($page_title, 'Login', 'login.php');
                }
                ?>
                <!-- PHP -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Dark Mode?</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Dark Mode</a></li>
                        <li><a class="dropdown-item" href="#">Light Mode</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- ******************************** SANDBOX ******************************** -->

    <div class="container">
        <!-- Script 3.2 - header.html -->