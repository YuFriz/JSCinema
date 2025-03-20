<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];

    // Pobranie nazwy filmu
    $stmt = $conn->prepare("SELECT name FROM movies WHERE id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $movie_name = $movie ? htmlspecialchars($movie['name']) : "Unknown Movie";

    $stmt->close();
} else {
    echo "Error!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review - <?= $movie_name ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-4">

<div class="card mx-auto" style="max-width: 600px;">
    <div class="card-header bg-primary text-white text-center">
        <h4>Add a Review for "<b><?= $movie_name ?></b>"</h4>
    </div>
    <div class="card-body">
        <form method="post" action="save_review.php">
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
