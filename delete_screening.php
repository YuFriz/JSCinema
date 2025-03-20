<?php
global $conn;
require 'session_manager.php';
require 'db_connection.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can delete screenings.");
}
$stmt->close();

$screening_id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM screenings WHERE id = ?");
$stmt->bind_param("i", $screening_id);

if ($stmt->execute()) {
    header("Location: auditorium_repertuar.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>

