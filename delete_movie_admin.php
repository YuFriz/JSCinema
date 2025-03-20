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
    die("Access denied! Only admins can delete movies.");
}
$stmt->close();





// Sprawdzenie, czy ID filmu zostało przekazane
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid movie ID!");
}

$movie_id = intval($_GET['id']);

// Pobranie ścieżek obrazów i wideo powiązanych z filmem
$stmt = $conn->prepare("SELECT video FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Movie not found!");
}

$movie = $result->fetch_assoc();
$stmt->close();

// Pobranie wszystkich obrazów powiązanych z filmem
$image_stmt = $conn->prepare("SELECT image_path FROM movie_images WHERE movie_id = ?");
$image_stmt->bind_param("i", $movie_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$images = $image_result->fetch_all(MYSQLI_ASSOC);
$image_stmt->close();

// Usunięcie obrazów z serwera
foreach ($images as $img) {
    if (file_exists($img['image_path'])) {
        unlink($img['image_path']);
    }
}

// Usunięcie wideo z serwera
if (!empty($movie['video']) && file_exists($movie['video'])) {
    unlink($movie['video']);
}

// Usunięcie folderu filmu
$movie_folder = "Movies/$movie_id";
if (is_dir($movie_folder)) {
    array_map('unlink', glob("$movie_folder/*.*")); // Usuwa wszystkie pliki w folderze
    rmdir($movie_folder); // Usuwa folder
}

// Usunięcie obrazów z bazy danych
$delete_images_stmt = $conn->prepare("DELETE FROM movie_images WHERE movie_id = ?");
$delete_images_stmt->bind_param("i", $movie_id);
$delete_images_stmt->execute();
$delete_images_stmt->close();

// Usunięcie filmu z bazy danych
$delete_movie_stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
$delete_movie_stmt->bind_param("i", $movie_id);

if ($delete_movie_stmt->execute()) {
    echo "<script>alert('Movie deleted successfully!'); window.location.href='movie_admin.php';</script>";
} else {
    echo "<script>alert('Error deleting movie.'); window.location.href='movie_admin.php';</script>";
}

$delete_movie_stmt->close();
$conn->close();
?>
