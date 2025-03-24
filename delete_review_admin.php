<?php
require 'db_connection.php';
require 'session_manager.php';

// Sprawdzenie uprawnień administratora
$admin_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin || $admin['Status'] !== 'admin') {
    die("Brak dostępu. Tylko administrator może usuwać recenzje.");
}

// Sprawdzenie, czy przekazano ID recenzji
if (!isset($_GET['id'])) {
    die("Brak ID recenzji.");
}
$review_id = intval($_GET['id']);

// Sprawdzenie, czy recenzja istnieje
$check_stmt = $conn->prepare("SELECT id FROM reviews_ratings WHERE id = ?");
$check_stmt->bind_param("i", $review_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows === 0) {
    $check_stmt->close();
    die("Recenzja nie istnieje.");
}
$check_stmt->close();

// Usunięcie recenzji
$delete_stmt = $conn->prepare("DELETE FROM reviews_ratings WHERE id = ?");
$delete_stmt->bind_param("i", $review_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    header("Location: reviews_and_ratings_admin.php?deleted=1");
    exit();
} else {
    $delete_stmt->close();
    die("Błąd podczas usuwania recenzji.");
}

$conn->close();
?>
