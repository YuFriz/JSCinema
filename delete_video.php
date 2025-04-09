<?php
require 'db_connection.php';
require 'session_manager.php';

// Sprawdź uprawnienia admina
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied!");
}

// Sprawdzenie ID filmu
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid movie ID.");
}

$movie_id = intval($_GET['id']);

// Pobierz ścieżkę wideo
$stmt = $conn->prepare("SELECT video FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();
$stmt->close();

if ($movie && !empty($movie['video']) && file_exists($movie['video'])) {
    unlink($movie['video']); // Usuń plik z serwera
}

// Wyczyść pole wideo z bazy
$update_stmt = $conn->prepare("UPDATE movies SET video = '' WHERE id = ?");
$update_stmt->bind_param("i", $movie_id);
$update_stmt->execute();
$update_stmt->close();

// Przekieruj z powrotem
header("Location: edit_movie_admin.php?id=$movie_id");
exit();
?>
