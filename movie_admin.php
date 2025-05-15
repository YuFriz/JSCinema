<?php
require 'db_connection.php';
require 'session_manager.php';

// Sprawdzenie czy użytkownik jest zalogowany i ma uprawnienia admina
if (!isset($_SESSION['user_id'])) {
    die("Access denied. No session.");
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can view movies.");
}

// Ustawienie sortowania
$sort_column = $_GET['sort'] ?? 'coming_date';
$sort_order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'DESC' : 'ASC';
$allowed_columns = ['name', 'status', 'coming_date', 'end_date'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'coming_date';
}

// Aktualizacja statusów na podstawie daty
$today = date('Y-m-d');
$update_sql = "UPDATE movies 
           SET status = CASE 
               WHEN coming_date <= ? AND end_date >= ? THEN 'already showing'
               WHEN coming_date > ? THEN 'soon in cinema'
               WHEN end_date < ? THEN 'screening ended'
               ELSE status
           END";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ssss", $today, $today, $today, $today);
$stmt->execute();
$stmt->close();

// Pobranie filmów
$query = "SELECT id, name, description, stars, author, video, movie_duration, plays, status, coming_date, end_date 
          FROM movies ORDER BY $sort_column $sort_order";
$movies = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<div class="container mt-4">
    <div class="mb-3">
        <input type="text" id="searchMoviesInput" class="form-control" placeholder="Search movies by title or author...">
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-center flex-grow-1">Movie List</h2>
        <a href="add_movie.php" class="btn btn-primary ms-3">Add New Movie</a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php while ($row = $movies->fetch_assoc()): ?>
            <div class="col">
                <div class="card-admin-movie h-100 shadow-sm">
                    <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                    <h6><?php echo htmlspecialchars($row['author']); ?></h6>
                    <div class="description-wrapper">
                        <span class="short-text"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 120, "...")); ?></span>
                        <span class="full-text d-none"><?php echo nl2br(htmlspecialchars($row['description'])); ?></span>
                        <a href="#" class="toggle-description">Show more</a>
                    </div>


                    <ul class="info-list">
                        <?php
                        $statusClass = '';
                        switch ($row['status']) {
                            case 'already showing':
                                $statusClass = 'status-showing';
                                break;
                            case 'soon in cinema':
                                $statusClass = 'status-soon';
                                break;
                            case 'screening ended':
                                $statusClass = 'status-ended';
                                break;
                            default:
                                $statusClass = '';
                        }
                        ?>
                        <li><strong>Status:</strong> <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['status']); ?></span></li>

                        <li><strong>Coming date:</strong> <?php echo $row['coming_date']; ?></li>
                        <li><strong>End date:</strong> <?php echo $row['end_date']; ?></li>
                        <li>
                            <strong>Stars:</strong>
                            <?php
                            $stmt = $conn->prepare("SELECT AVG(star) as avg_star, COUNT(*) as count_reviews FROM reviews_ratings WHERE movie_id = ?");
                            $stmt->bind_param("i", $row['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $rating = $result->fetch_assoc();
                            $stmt->close();
                            if ($rating['count_reviews'] > 0) {
                                echo round($rating['avg_star'], 1) . ' ⭐ (' . $rating['count_reviews'] . ' review' . ($rating['count_reviews'] > 1 ? 's' : '') . ')';
                            } else {
                                echo 'No ratings';
                            }
                            ?>
                        </li>
                    </ul>

                    <div class="card-buttons">
                        <a href="edit_movie_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_movie_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".toggle-description").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                const wrapper = this.closest(".description-wrapper");
                const shortText = wrapper.querySelector(".short-text");
                const fullText = wrapper.querySelector(".full-text");

                if (fullText.classList.contains("d-none")) {
                    shortText.classList.add("d-none");
                    fullText.classList.remove("d-none");
                    this.textContent = "Show less";
                } else {
                    shortText.classList.remove("d-none");
                    fullText.classList.add("d-none");
                    this.textContent = "Show more";
                }
            });
        });
    });
</script>


<script>
    document.getElementById("searchMoviesInput").addEventListener("input", function () {
        const filter = this.value.toLowerCase();
        const cards = document.querySelectorAll(".card-admin-movie");

        cards.forEach(card => {
            const title = card.querySelector("h5").textContent.toLowerCase();
            const author = card.querySelector("h6").textContent.toLowerCase();
            card.parentElement.style.display = (title.includes(filter) || author.includes(filter)) ? "block" : "none";
        });
    });
</script>

</body>
</html>
