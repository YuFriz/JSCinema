<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];
    $rating = $_POST['star'];
    $review = trim($_POST['review']);

    if ($rating < 1 || $rating > 5) {
        echo "Nieprawidłowa ocena!";
        exit;
    }

    // Sprawdzenie, czy użytkownik już ocenił ten seans
    $check_sql = "SELECT id FROM reviews_ratings WHERE user_id = ? AND movie_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $movie_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Już dodałeś recenzję dla tego seansu.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Wstawienie nowej recenzji do tabeli reviews_ratings
    $insert_sql = "INSERT INTO reviews_ratings (user_id, movie_id, star, review, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $review);

    if (!$stmt->execute()) {
        echo "Błąd podczas dodawania recenzji.";
        exit;
    }
    $stmt->close();

    // Aktualizacja średniej oceny w tabeli movies
    $update_sql = "UPDATE movies 
                   SET stars = (SELECT AVG(star) FROM reviews_ratings WHERE movie_id = ?) 
                   WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $movie_id, $movie_id);

    if ($stmt->execute()) {
        echo "Recenzja została dodana!";
        header("Location: reviews_and_ratings.php?movie_id=$movie_id"); // Przekierowanie po sukcesie
        exit;
    } else {
        echo "Błąd podczas aktualizacji oceny filmu.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Nieprawidłowe żądanie.";
}
?>
