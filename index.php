<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SESSION['accTypes'] == "mainAdmin") {
    header("location:admin/index.php");
} else if ($_SESSION['accTypes'] == "teachers") {
    header("location:teachers/index.php");
}
