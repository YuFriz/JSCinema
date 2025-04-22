<?php
require 'db_connection.php';
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
    die("Access denied! Only admins can look on movies.");
}



// Ustawienie domyślnego sortowania
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'coming_date';
$sort_order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';

// Dozwolone kolumny do sortowania
$allowed_columns = ['name', 'status', 'coming_date', 'end_date'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'coming_date';
}

// Pobranie filmów z bazy danych z sortowaniem
$query = "SELECT id, name, description, stars, author, video, movie_duration, plays, status, coming_date, end_date FROM movies ORDER BY $sort_column $sort_order";
$movies = $conn->query($query);

// Fetch images for movies
define('IMAGE_QUERY', "SELECT image_path FROM movie_images WHERE movie_id = ?");

// Pobranie gatunków dla filmów
define('GENRE_QUERY', "SELECT g.name FROM movie_genres mg JOIN genres g ON mg.genre_id = g.id WHERE mg.movie_id = ?");

// Ikona sortowania
function get_sort_icon($column, $sort_column, $sort_order) {
    if ($column === $sort_column) {
        return $sort_order === 'ASC' ? '↑' : '↓';
    }
    return '';
}


// Automatyczna aktualizacja statusów filmów na podstawie daty
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<!-- Main Content -->


<div class="container mt-4">
    <!-- Wyszukiwanie filmów -->
    <div class="mb-3">
        <input type="text" id="searchMoviesInput" class="form-control" placeholder="Search movies by title or author...">
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-center">Movie List</h2>
        <a href="add_movie.php" class="btn btn-primary">Add New Movie</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-bordered align-middle text-center" id="moviesTable">
        <thead class="table-dark">
            <tr>
                <th><a href="?sort=name&order=<?php echo $sort_order === 'ASC' ? 'desc' : 'asc'; ?>" class="text-light">Name <?php echo get_sort_icon('name', $sort_column, $sort_order); ?></a></th>
                <th>Description</th>
                <th>Stars</th>
                <th>Author</th>
                <th>Images</th>
                <th>Genres</th>
                <th>Video</th>
                <th>Duration</th>
                <th>Plays</th>
                <th><a href="?sort=status&order=<?php echo $sort_order === 'ASC' ? 'desc' : 'asc'; ?>" class="text-light">Status <?php echo get_sort_icon('status', $sort_column, $sort_order); ?></a></th>
                <th><a href="?sort=coming_date&order=<?php echo $sort_order === 'ASC' ? 'desc' : 'asc'; ?>" class="text-light">Coming Date <?php echo get_sort_icon('coming_date', $sort_column, $sort_order); ?></a></th>
                <th><a href="?sort=end_date&order=<?php echo $sort_order === 'ASC' ? 'desc' : 'asc'; ?>" class="text-light">End Date <?php echo get_sort_icon('end_date', $sort_column, $sort_order); ?></a></th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $movies->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <div class="description-wrapper">
                            <span class="short-text"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 100, "...")); ?></span>
                            <span class="full-text d-none"><?php echo htmlspecialchars($row['description']); ?></span>
                            <a href="#" class="toggle-description text-primary" style="display:block; font-size: 0.9rem;">Show more</a>
                        </div>
                    </td>

                    <td>
                        <?php
                        $stmt = $conn->prepare("SELECT AVG(star) as avg_star, COUNT(*) as count_reviews FROM reviews_ratings WHERE movie_id = ?");
                        $stmt->bind_param("i", $row['id']);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $rating = $result->fetch_assoc();
                        $stmt->close();

                        if ($rating['count_reviews'] > 0) {
                            $avg = round($rating['avg_star'], 1);
                            echo $avg . ' ⭐ (' . $rating['count_reviews'] . ' review' . ($rating['count_reviews'] > 1 ? 's' : '') . ')';
                        } else {
                            echo 'No ratings';
                        }
                        ?>
                    </td>

                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td>
                        <div class="d-flex flex-wrap justify-content-center">
                            <?php
                            $stmt = $conn->prepare(IMAGE_QUERY);
                            $stmt->bind_param("i", $row['id']);
                            $stmt->execute();
                            $images = $stmt->get_result();
                            while ($img = $images->fetch_assoc()) {
                                echo '<img src="' . htmlspecialchars($img['image_path']) . '" class="img-thumbnail m-1" width="80" height="80" alt="Movie Image">';
                            }
                            $stmt->close();
                            ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        $stmt = $conn->prepare(GENRE_QUERY);
                        $stmt->bind_param("i", $row['id']);
                        $stmt->execute();
                        $genres = $stmt->get_result();
                        $genre_list = [];
                        while ($genre = $genres->fetch_assoc()) {
                            $genre_list[] = htmlspecialchars($genre['name']);
                        }
                        echo implode(', ', $genre_list);
                        $stmt->close();
                        ?>
                    </td>
                    <td><a href="<?php echo $row['video']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">Watch</a></td>
                    <td><?php echo $row['movie_duration']; ?> min</td>
                    <td><?php echo $row['plays']; ?></td>
                    <td>
                        <?php
                        $statusClass = '';
                        switch ($row['status']) {
                            case 'already showing': $statusClass = 'status-showing'; break;
                            case 'soon in cinema': $statusClass = 'status-soon'; break;
                            case 'screening ended': $statusClass = 'status-ended'; break;
                        }
                        ?>
                        <span class="badge status-badge <?= $statusClass ?>">
        <?= htmlspecialchars($row['status']) ?>
    </span>
                    </td>

                    <td><?php echo $row['coming_date']; ?></td>
                    <td><?php echo $row['end_date']; ?></td>
                    <td>
                        <div class="d-flex flex-column align-items-center gap-2">
                            <a href="edit_movie_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete_movie_admin.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Opis toggle
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

        // 🔍 Filtrowanie filmów
        const input = document.getElementById("searchMoviesInput");
        input.addEventListener("keyup", function () {
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll("#moviesTable tbody tr");

            rows.forEach(row => {
                const title = row.cells[0].innerText.toLowerCase();    // Name
                const author = row.cells[3].innerText.toLowerCase();   // Author

                if (title.includes(filter) || author.includes(filter)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });
</script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
