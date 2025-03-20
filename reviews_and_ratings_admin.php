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
    <h2 class="mb-4">Reviews and Ratings</h2>
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Movie</th>
            <th>Review</th>
            <th>Stars</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['imie'] . ' ' . $row['nazwisko']); ?></td>
                    <td><?php echo htmlspecialchars($row['movie_name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($row['review'])); ?></td>
                    <td>
                        <?php for ($i = 0; $i < $row['star']; $i++): ?>
                            ⭐
                        <?php endfor; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No reviews available</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php
$conn->close();
?>
