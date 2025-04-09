<?php
require 'db_connection.php'; // Database connection file
require 'session_manager.php';

// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can look on reviews and ratings.");
}
$stmt->close();



// Fetch data from reviews_ratings table, including user and movie details
$sql = "SELECT r.id, u.imie, u.nazwisko, m.name AS movie_name, r.review, r.star 
        FROM reviews_ratings r 
        JOIN users u ON r.user_id = u.id
        JOIN movies m ON r.movie_id = m.id";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews and Ratings</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card card-review-admin shadow p-4 rounded-4">
        <h3 class="text-center mb-4">📝 Reviews and Ratings</h3>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-review-admin text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Movie</th>
                    <th>Review</th>
                    <th>Stars</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold text-muted"><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']); ?></td>
                            <td><span class="badge badge-review-admin"><?= htmlspecialchars($row['movie_name']); ?></span></td>
                            <td class="text-start"><?= nl2br(htmlspecialchars($row['review'])); ?></td>
                            <td>
                                <?php for ($i = 0; $i < $row['star']; $i++): ?>
                                    <span class="text-warning">⭐</span>
                                <?php endfor; ?>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a href="edit_review_admin.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mr-2">
                                        ✏️ Edit
                                    </a>
                                    <a href="delete_review_admin.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?');">
                                        ❌ Delete
                                    </a>
                                </div>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No reviews available</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


</body>
</html>
<?php
$conn->close();
?>
