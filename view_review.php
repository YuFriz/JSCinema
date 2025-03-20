<?php
session_start();
require 'db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>You must be logged in to view your review.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if review_id is provided in the URL
if (!isset($_GET['review_id']) || empty($_GET['review_id'])) {
    echo "<div class='alert alert-warning text-center'>Invalid review ID.</div>";
    exit;
}

$review_id = intval($_GET['review_id']);

// Fetch the review from the `reviews_ratings` table
$sql = "SELECT r.star, r.review, r.created_at, m.name AS movie_name
        FROM reviews_ratings r
        JOIN movies m ON r.movie_id = m.id
        WHERE r.id = ? AND r.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();

if (!$review) {
    echo "<div class='alert alert-danger text-center'>Review not found or you don't have permission to view it.</div>";
    exit;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Review - <?= htmlspecialchars($review['movie_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">

<div class="card-review shadow-lg p-4" style="max-width: 600px; width: 100%;">
    <div class="card-header bg-primary text-white text-center">
        <h4>Review for <b><?= htmlspecialchars($review['movie_name']) ?></b></h4>
    </div>
    <div class="card-body">
        <p class="text-center">
            <strong>Rating:</strong>
            <span class="stars text-warning fs-4"><?= str_repeat('★', $review['star']) . str_repeat('☆', 5 - $review['star']) ?></span>
        </p>
        <p class="text-muted"><strong>Review:</strong></p>
        <blockquote class="blockquote p-3 bg-light border-start border-primary border-3">
            <?= nl2br(htmlspecialchars($review['review'])) ?>
        </blockquote>
        <p class="text-end text-muted small"><i>Reviewed on: <?= $review['created_at'] ?></i></p>
    </div>
    <div class="card-footer text-center">
        <a href="reviews_and_ratings.php" class="btn btn-dark btn-sm">Back to Reviews</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
