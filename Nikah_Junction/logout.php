<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

header("Location: index.html"); // Change to your desired page
exit;
?>
