<?php
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Contact Form</title>
</head>
<body>
    <?php # Script 12.x - contact.php

    session_start(); // Start the session.
    
    $page_title = 'Contact Form';
    include('includes/header.php'); // include the header
    
    echo "<h1>$page_title</h1>";

    // Check for form submission:
    if($_SERVER['REQUEST_METHOD'] == 'POST') {

        /** The function takes one argument: the string.
         * The function returns a clean version of the string.
         */
        function spam_scrubber($value) {

            // List of very bad values:
            $very_bad = ['to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:', 'risk-free', 'big bucks', 'act now'];

            // If any of the very bad strings are in the submitted value, return an empty string:
            foreach($very_bad as $v) {
                if(stripos($value, $v) !== false) return '';
            }

            // Replace any newline characters with spaces:
            $value = str_replace(["\r", "\n", "%0a", "%0d"], ' ', $value);

            // Return the value:
            return trim($value);
        } // End of spam_scrubber() function.

        // clean the comments of any spam
        $scrubbed_comments = spam_scrubber($_POST['comments']);

        // Minimal form validation:
        if(!empty($scrubbed_comments)) {

            // Create the body:
            $body = "Hello, {$_SESSION['first_name']} {$_SESSION['last_name']} at {$_SESSION['email']},\n\nThank you for these comments:\n\n$scrubbed_comments\n\nWe will respond as soon as we can.\n\nThe Staff at PHP Stuff";

            // Make it no longer than 70 characters long:
            $body = wordwrap($body, 70);

            // Send the email
            mail('wheelerchristian33@gmail.com', 'Contact Form Submission', $body, "From: {$_SESSION['email']}");

            // Print a message:
            echo "<p><em>Thank you for your comments, {$_SESSION['first_name']} {$_SESSION['last_name']}, please check your email.</em></p>";

            // Clear $_POST (so that the form's not sticky):
            $_POST = [];

        } else {
            echo '<p style="font-weight: bold; color: #C00">This looks like spam, please try again.</p>';
        }
    } // End of main isset() IF
    ?>

    <p>Please type your comments here.</p>
    <form action="contact.php" method="post">
        <p><textarea name="comments" cols="30" rows="5"><?php if(isset($_POST['comments'])) echo $_POST['comments']; ?></textarea></p>
        <p><input type="submit" name="submit" value="Send!"></p>
    </form>
</body>
</html>