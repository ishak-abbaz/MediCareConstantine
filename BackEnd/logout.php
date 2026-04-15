<?php
session_start();

// unset all session variables
$_SESSION = array();

// destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// destroy the session
session_destroy();

// redirect to login page
header('Location: login.php');
exit();
?>