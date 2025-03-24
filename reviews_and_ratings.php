<?php
session_start();
require 'db_connection.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>You must be logged in to view your past screenings and reviews.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];
$current_time = date('Y-m-d H:i:s');

// Check which filter option is selected
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Base SQL query to fetch unique movies from past screenings
$sql = "SELECT DISTINCT m.id AS movie_id, m.name AS movie_name,
               (SELECT id FROM reviews_ratings rr WHERE rr.user_id = ? AND rr.movie_id = m.id LIMIT 1) AS review_id
        FROM purchased_tickets pt
        JOIN screenings s ON pt.screening_id = s.id
        JOIN movies m ON s.movie_id = m.id
        WHERE pt.user_id = ? AND CONCAT(s.screening_date, ' ', s.start_time) <= ?
        ORDER BY m.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $user_id, $current_time);
$stmt->execute();
$result = $stmt->get_result();

$watched_movies = [];
while ($row = $result->fetch_assoc()) {
    // Apply filter based on the selected option
    if ($filter === 'no_review' && $row['review_id'] !== null) {
        continue; // Skip if the movie already has a review
    }
    if ($filter === 'my_reviews' && $row['review_id'] === null) {
        continue; // Skip if the movie does not have a review
    }
    $watched_movies[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Screenings and Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Profile Panel</a>
    </div>
</nav>

<div class="container-review mt-4 d-flex flex-column align-items-center">
    <h2 class="text-center mb-4">🎬 Past Screenings</h2>

    <!-- Filter buttons -->
    <div class="text-center mb-4">
        <a href="?filter=all" class="btn <?= ($filter === 'all') ? 'btn-dark' : 'btn-outline-dark' ?>">Show All</a>
        <a href="?filter=no_review" class="btn <?= ($filter === 'no_review') ? 'btn-dark' : 'btn-outline-dark' ?>">Show Without Review</a>
        <a href="?filter=my_reviews" class="btn <?= ($filter === 'my_reviews') ? 'btn-dark' : 'btn-outline-dark' ?>">Show My Reviews</a>
    </div>

    <?php if (!empty($watched_movies)): ?>
        <div class="table-responsive-review">
            <table class="table table-striped table-hover reviews-table text-center">
                <thead class="table-dark">
                <tr>
                    <th class="text-center">Movie</th>
                    <th class="text-center">Options</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($watched_movies as $movie): ?>
                    <tr>
                        <td><?= htmlspecialchars($movie['movie_name']) ?></td>
                        <td class="d-flex justify-content-center gap-2">
                            <?php if ($movie['review_id'] !== null): ?>
                                <a href="view_review.php?review_id=<?= $movie['review_id'] ?>" class="btn btn-success btn-sm"><i class="fa fa-eye"></i> View</a>
                                <a href="edit_review.php?review_id=<?= $movie['review_id'] ?>" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                <form method="post" action="delete_review.php" class="d-inline">
                                    <input type="hidden" name="review_id" value="<?= $movie['review_id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this review?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="add_review.php" class="d-inline">
                                    <input type="hidden" name="movie_id" value="<?= $movie['movie_id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Review</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    <?php else: ?>
        <p class="text-muted text-center">No past screenings matching the selected filter.</p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
