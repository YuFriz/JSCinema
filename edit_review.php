<?php
session_start();
require 'db_connection.php';

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>You must be logged in to edit your review.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Sprawdzenie, czy review_id jest przekazane w URL lub POST (dla zapisu zmian)
if (!isset($_GET['review_id']) && !isset($_POST['review_id'])) {
    echo "<div class='alert alert-warning text-center'>Invalid review ID.</div>";
    exit;
}

$review_id = isset($_GET['review_id']) ? intval($_GET['review_id']) : intval($_POST['review_id']);
$success_message = "";
$error_message = "";

// Pobranie recenzji użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT r.star, r.review, m.id AS movie_id, m.name AS movie_name 
            FROM reviews_ratings r
            JOIN movies m ON r.movie_id = m.id
            WHERE r.id = ? AND r.user_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $review = $result->fetch_assoc();

    if (!$review) {
        echo "<div class='alert alert-danger text-center'>Review not found or you don't have permission to edit it.</div>";
        exit;
    }

    $stmt->close();
}

// Przetwarzanie formularza edycji (jeśli użytkownik zapisuje zmiany)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $movie_id = $_POST['movie_id'];
    $rating = $_POST['star'];
    $review_text = trim($_POST['review']);

    // Sprawdzenie, czy użytkownik ma prawo edytować tę recenzję
    $stmt = $conn->prepare("SELECT id FROM reviews_ratings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $review_id, $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $error_message = "You do not have permission to edit this review.";
    } else {
        // Aktualizacja recenzji
        $update_sql = "UPDATE reviews_ratings SET star = ?, review = ?, created_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("isi", $rating, $review_text, $review_id);

        if ($stmt->execute()) {
            // Aktualizacja średniej oceny filmu
            $update_avg = "UPDATE movies 
                        SET stars = (SELECT IFNULL(AVG(star), 0) FROM reviews_ratings WHERE movie_id = ?) 
                        WHERE id = ?";
            $stmt = $conn->prepare($update_avg);
            $stmt->bind_param("ii", $movie_id, $movie_id);
            $stmt->execute();

            $success_message = "Review updated successfully!";
        } else {
            $error_message = "Error updating review.";
        }
    }
    $stmt->close();
}

// Pobranie najnowszych danych do wyświetlenia w formularzu po edycji
$sql = "SELECT r.star, r.review, m.id AS movie_id, m.name AS movie_name 
        FROM reviews_ratings r
        JOIN movies m ON r.movie_id = m.id
        WHERE r.id = ? AND r.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - <?= htmlspecialchars($review['movie_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container mt-4">

<div class="card mx-auto" style="max-width: 600px;">
    <div class="card-header bg-warning text-white text-center">
        <h4>Edit Review for "<b><?= htmlspecialchars($review['movie_name']) ?></b>"</h4>
    </div>
    <div class="card-body">

        <!-- Wyświetlenie komunikatów -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success text-center"><?= $success_message ?></div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger text-center"><?= $error_message ?></div>
        <?php endif; ?>

        <form method="post" action="edit_review.php">
            <input type="hidden" name="review_id" value="<?= $review_id ?>">
            <input type="hidden" name="movie_id" value="<?= $review['movie_id'] ?>">

            <div class="mb-3">
                <label class="form-label">Rating (1-5):</label>
                <select name="star" class="form-select" required>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= ($review['star'] == $i) ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Review:</label>
                <textarea name="review" class="form-control" rows="4" required><?= htmlspecialchars($review['review']) ?></textarea>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-warning">Update Review</button>
                <a href="reviews_and_ratings.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
