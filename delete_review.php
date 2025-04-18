<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to delete a review.");
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['review_id']) || !is_numeric($_POST['review_id'])) {
    die("Invalid review ID.");
}

$review_id = (int)$_POST['review_id'];

$check_stmt = $conn->prepare("SELECT id FROM reviews_ratings WHERE id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $review_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    die("Review not found or you do not have permission to delete it.");
}

$delete_stmt = $conn->prepare("DELETE FROM reviews_ratings WHERE id = ?");
$delete_stmt->bind_param("i", $review_id);
$delete_stmt->execute();

$delete_stmt->close();
$check_stmt->close();
$conn->close();

header("Location: reviews_and_ratings.php?filter=my_reviews");
exit;
?>
