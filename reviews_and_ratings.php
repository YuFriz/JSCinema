<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='alert alert-danger text-center'>You must be logged in to view your past screenings and reviews.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];
$current_time = date('Y-m-d H:i:s');
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'no_review';

$sql = "SELECT DISTINCT 
            m.id AS movie_id, 
            m.name AS movie_name,
            (SELECT image_path FROM movie_images WHERE movie_id = m.id ORDER BY id ASC LIMIT 1) AS movie_image,
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
    if ($filter === 'no_review' && $row['review_id'] !== null) continue;
    if ($filter === 'my_reviews' && $row['review_id'] === null) continue;
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

<div class="container mt-4">
    <h2 class="text-center mb-4">🎬 Past Screenings</h2>

    <div class="d-flex justify-content-center gap-3 mb-4">
        <a href="?filter=no_review" class="btn <?= ($filter === 'no_review') ? 'btn-dark' : 'btn-outline-dark' ?>">Without Review</a>
        <a href="?filter=my_reviews" class="btn <?= ($filter === 'my_reviews') ? 'btn-dark' : 'btn-outline-dark' ?>">My Reviews</a>
        <a href="?filter=all" class="btn <?= ($filter === 'all') ? 'btn-dark' : 'btn-outline-dark' ?>">All</a>
    </div>

    <div class="row justify-content-center">
        <?php if (!empty($watched_movies)): ?>
            <?php foreach ($watched_movies as $movie): ?>
                <div class="col-md-6 col-lg-4 mb-4 d-flex justify-content-center">
                    <div class="userprofile-review-card">
                        <?php $img = !empty($movie['movie_image']) ? htmlspecialchars($movie['movie_image']) : 'placeholder.jpg'; ?>
                        <img src="<?= $img ?>" class="img-fluid rounded mb-3" alt="Movie image">
                        <h5><i class="fa-solid fa-clapperboard"></i> <?= htmlspecialchars($movie['movie_name']) ?></h5>
                        <div class="mt-3">
                            <?php if ($movie['review_id'] !== null): ?>
                                <a href="view_review.php?review_id=<?= $movie['review_id'] ?>" class="btn btn-outline-success btn-sm w-100 mb-2">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="edit_review.php?review_id=<?= $movie['review_id'] ?>" class="btn btn-outline-warning btn-sm w-100 mb-2">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <form method="post" action="delete_review.php">
                                    <input type="hidden" name="review_id" value="<?= $movie['review_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this review?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="add_review.php">
                                    <input type="hidden" name="movie_id" value="<?= $movie['movie_id'] ?>">
                                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fa fa-plus"></i> Add Review
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
