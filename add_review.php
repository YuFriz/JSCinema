<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>You must be logged in to access this page.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review']) && isset($_POST['star'])) {
    // Obsługa zapisu recenzji
    $movie_id = $_POST['movie_id'];
    $rating = (int)$_POST['star'];
    $review = trim($_POST['review']);

    if ($rating < 1 || $rating > 5) {
        echo "Nieprawidłowa ocena!";
        exit;
    }

    // Sprawdź, czy recenzja już istnieje
    $stmt = $conn->prepare("SELECT id FROM reviews_ratings WHERE user_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $user_id, $movie_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Już dodałeś recenzję dla tego filmu.";
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();

    // Zapisz recenzję
    $stmt = $conn->prepare("INSERT INTO reviews_ratings (user_id, movie_id, star, review, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $user_id, $movie_id, $rating, $review);
    $stmt->execute();
    $stmt->close();

    // Aktualizuj średnią ocenę filmu
    $stmt = $conn->prepare("UPDATE movies SET stars = (SELECT AVG(star) FROM reviews_ratings WHERE movie_id = ?) WHERE id = ?");
    $stmt->bind_param("ii", $movie_id, $movie_id);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    header("Location: reviews_and_ratings.php?filter=no_review");
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['movie_id'])) {
    // Wyświetlenie formularza recenzji
    $movie_id = $_POST['movie_id'];

    // Pobierz nazwę filmu
    $stmt = $conn->prepare("SELECT name FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $movie_name = $movie ? htmlspecialchars($movie['name']) : "Unknown Movie";
    $stmt->close();
} else {
    echo "Nieprawidłowe żądanie.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Review - <?= $movie_name ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-5">

<div class="card mx-auto" style="max-width: 600px;">
    <div class="card-header bg-primary text-white text-center">
        <h4>Add a Review for "<b><?= $movie_name ?></b>"</h4>
    </div>
    <div class="card-body">
        <form method="post" action="add_review.php">
            <input type="hidden" name="movie_id" value="<?= $movie_id ?>">

            <div class="mb-3">
                <label class="form-label">Rating (1-5):</label>
                <select name="star" class="form-select" required>
                    <option value="1">★</option>
                    <option value="2">★★</option>
                    <option value="3">★★★</option>
                    <option value="4">★★★★</option>
                    <option value="5">★★★★★</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Review:</label>
                <textarea name="review" class="form-control" rows="4" required></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success">Submit Review</button>
                <a href="reviews_and_ratings.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
