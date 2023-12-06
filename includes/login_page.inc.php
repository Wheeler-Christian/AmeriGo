<?php # Script 12.1 - login_page.inc.php
    // This page prints any errors associated with logging in
    // and it creates the entire login page, including the form.

    // Include the header:
    $page_title = 'Login';
    include('includes/header.php');

    // Print any error messages, if they exist:
    if(isset($errors) && !empty($errors)) {
        echo '<h1>Error!</h1>
        <p class="error">The following error(s) occurred:<br>';
        foreach($errors as $msg) {
            echo " - $msg<br>\n";
        }
        echo '</p><p>Please try again.</p>';
    }
?>

<!-- Display the form: -->
<h1>Login</h1>
<form action="login.php" method="post" novalidate>
    <?php
    if($_SERVER['REQUEST_METHOD'] == 'POST') { // if the form has been submitted, then we might make it sticky to display the email
        if(isset($_POST['email'])) { // if the email was entered
            // Make it sticky
            echo '<p>Email Address: <input type="email" name="email" size="20" maxlength="60" value="' . $_POST['email'] . '"></p>';
        } else { // The email was not entered
            // Do not make it sticky
            echo '<p>Email Address: <input type="email" name="email" size="20" maxlength="60"></p>';
        }
    } else { // the form has not been submitted
        // Do not make it sticky
        echo '<p>Email Address: <input type="email" name="email" size="20" maxlength="60"></p>';
    }
    ?>    
    <p>Password <input type="password" name="pass" size="20" maxlenth="20"></p>
    <p><input type="submit" name="submit" value="Login"></p>
</form>

<?php include('includes/footer.html'); ?>