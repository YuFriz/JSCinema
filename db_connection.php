<?php
$host = 'localhost';
$dbname = 'cinemajs';
$username = 'cinema_user';
$password = 'abc';


$conn = new mysqli($host, $username, $password, $dbname);

// Sprawdzamy połączenie
if ($conn->connect_error) {
    die("Database connection error:" . $conn->connect_error);
}

?>
