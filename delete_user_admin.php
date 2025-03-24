<?php
require 'session_manager.php';
require 'db_connection.php';

if (!isset($_GET['id'])) {
    die("Brak ID użytkownika.");
}

$delete_user_id = intval($_GET['id']);

// Sprawdzenie, czy obecnie zalogowany użytkownik to admin
$current_user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$stmt->close();

if (!$current_user || $current_user['Status'] !== 'admin') {
    die("Brak dostępu.");
}

// Zabezpieczenie: admin nie może usunąć samego siebie
if ($delete_user_id === $current_user_id) {
    die("Nie możesz usunąć samego siebie!");
}

// Sprawdzenie, czy użytkownik do usunięcia istnieje
$check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$check_stmt->bind_param("i", $delete_user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
if ($check_result->num_rows === 0) {
    $check_stmt->close();
    die("Użytkownik nie istnieje.");
}
$check_stmt->close();

// Usunięcie użytkownika
$delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete_stmt->bind_param("i", $delete_user_id);

if ($delete_stmt->execute()) {
    $delete_stmt->close();
    header("Location: users-admin.php?deleted=1");
    exit();
} else {
    $delete_stmt->close();
    die("Błąd podczas usuwania użytkownika.");
}

$conn->close();
?>
