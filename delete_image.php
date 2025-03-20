<?php
require 'db_connection.php';
require 'session_manager.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can delete images from movies.");
}
$stmt->close();




// Sprawdzenie, czy ID obrazu i ID filmu są przekazane
if (!isset($_GET['id']) || !isset($_GET['movie_id'])) {
    die("Invalid request!");
}

$image_id = intval($_GET['id']);
$movie_id = intval($_GET['movie_id']);

// Pobranie ścieżki obrazu z bazy danych
$stmt = $conn->prepare("SELECT image_path FROM movie_images WHERE id = ? AND movie_id = ?");
$stmt->bind_param("ii", $image_id, $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Image not found!");
}

$image = $result->fetch_assoc();
$image_path = $image['image_path'];
$stmt->close();

// Usunięcie pliku obrazu z serwera
if (file_exists($image_path)) {
    unlink($image_path);
}

// Usunięcie rekordu z bazy danych
$delete_stmt = $conn->prepare("DELETE FROM movie_images WHERE id = ?");
$delete_stmt->bind_param("i", $image_id);

if ($delete_stmt->execute()) {
    echo "<script>alert('Image deleted successfully!'); window.location.href='edit_movie_admin.php?id=$movie_id';</script>";
} else {
    echo "<script>alert('Error deleting image.'); window.location.href='edit_movie_admin.php?id=$movie_id';</script>";
}

$delete_stmt->close();
$conn->close();
?>
