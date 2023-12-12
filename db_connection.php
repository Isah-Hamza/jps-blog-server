<?php

$host = "localhost"; // Server name
$user = "root"; // Database username
$password = ""; // Database password
$database = "jps"; //Database name

// Create a database connection
$connection = mysqli_connect($host, $user, $password, $database);

// Check if the connection was successful
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

?>