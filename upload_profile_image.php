<?php
require 'session_manager.php';

$conn = new mysqli('localhost', 'root', '', 'cinemajs');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/";

    // Tworzenie folderu, jeśli nie istnieje
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["profile_image"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Sprawdzenie formatu
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        die("Błąd: Dozwolone formaty to JPG, JPEG, PNG, GIF.");
    }

    // Przesyłanie pliku
    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        // Zapisujemy tylko nazwę pliku w bazie danych
        $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $file_name, $user_id);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Błąd podczas przesyłania pliku.");
    }
}

$conn->close();
header("Location: profile.php");
exit();

?>
