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
    die("Access denied! Only admins can edit movies.");
}
$stmt->close();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid movie ID!");
}
$movie_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Movie not found!");
}
$movie = $result->fetch_assoc();
$stmt->close();

$image_stmt = $conn->prepare("SELECT id, image_path FROM movie_images WHERE movie_id = ?");
$image_stmt->bind_param("i", $movie_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$images = $image_result->fetch_all(MYSQLI_ASSOC);
$image_stmt->close();

$genre_stmt = $conn->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
$genre_stmt->bind_param("i", $movie_id);
$genre_stmt->execute();
$genre_result = $genre_stmt->get_result();
$selected_genres = [];
while ($row = $genre_result->fetch_assoc()) {
    $selected_genres[] = $row['genre_id'];
}
$genre_stmt->close();

$genreSql = "SELECT * FROM genres";
$genreResult = $conn->query($genreSql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $stars = $_POST['stars'];
    $author = $_POST['author'];
    $movie_duration = intval($_POST['movie_duration']);
    $plays = $_POST['plays'];
    $coming_date = $_POST['coming_date'];
    $end_date = $_POST['end_date'];
    $genres = isset($_POST['genre']) ? $_POST['genre'] : [];

    $today = date('Y-m-d');
    if ($coming_date > $today) {
        $status = 'soon in cinema';
    } elseif ($coming_date <= $today && $end_date >= $today) {
        $status = 'already showing';
    } elseif ($end_date < $today) {
        $status = 'screening ended';
    } else {
        $status = 'unknown';
    }

    $update_stmt = $conn->prepare("UPDATE movies SET name = ?, description = ?, stars = ?, author = ?, video = ?, movie_duration = ?, plays = ?, status = ?, coming_date = ?, end_date = ? WHERE id = ?");
    $update_stmt->bind_param("sssssissssi", $name, $description, $stars, $author, $video, $movie_duration, $plays, $status, $coming_date, $end_date, $movie_id);

    $conn->query("DELETE FROM movie_genres WHERE movie_id = $movie_id");
    foreach ($genres as $genre_id) {
        $conn->query("INSERT INTO movie_genres (movie_id, genre_id) VALUES ($movie_id, $genre_id)");
    }

    if ($update_stmt->execute()) {
        echo "<script>alert('Movie updated successfully!'); window.location.href='movie_admin.php';</script>";
    } else {
        echo "<script>alert('Error updating movie.');</script>";
    }
    $update_stmt->close();

    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $index => $filename) {
            if ($_FILES['images']['error'][$index] == 0) {
                $file_name = "$movie_id-" . basename($filename);
                $destination = "movies/$movie_id/$file_name";

                if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $destination)) {
                    $image_path = "movies/$movie_id/$file_name";
                    $sql_image = "INSERT INTO movie_images (movie_id, image_path) VALUES ('$movie_id', '$image_path')";
                    $conn->query($sql_image);
                }
            }
        }
    }

    if (!empty($_FILES['video']['name'])) {
        $videoName = basename($_FILES['video']['name']);
        $videoPath = "movies/$movie_id/" . $videoName;

        if (!is_dir("movies/$movie_id")) {
            mkdir("movies/$movie_id", 0777, true);
        }

        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPath)) {
            $updateVideoStmt = $conn->prepare("UPDATE movies SET video = ? WHERE id = ?");
            $updateVideoStmt->bind_param("si", $videoPath, $movie_id);
            $updateVideoStmt->execute();
            $updateVideoStmt->close();
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Movie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="movie_admin.php" class="btn btn-outline-light">Back to Movie List</a>
    </div>
</nav>
<div class="container mt-4">
    <h2 class="text-center mb-4">🎬 Edit Movie</h2>

    <form method="POST" enctype="multipart/form-data">
        <div class="row g-4">

            <!-- Left Column -->
            <div class="col-md-6">
                <div class="card-edit-movie-admin p-4 shadow-sm">
                    <h5 class="mb-3">📄 Basic Info</h5>

                    <div class="mb-3">
                        <label for="name" class="form-label">Movie Name:</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($movie['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea id="description" name="description" class="form-control" rows="5" required><?= htmlspecialchars($movie['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="stars" class="form-label">Stars:</label>
                        <input type="text" id="stars" name="stars" class="form-control" value="<?= htmlspecialchars($movie['stars']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="author" class="form-label">Author:</label>
                        <input type="text" id="author" name="author" class="form-control" value="<?= htmlspecialchars($movie['author']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="plays" class="form-label">Cast:</label>
                        <input type="text" id="plays" name="plays" class="form-control" value="<?= htmlspecialchars($movie['plays']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col">
                            <label for="coming_date" class="form-label">Coming Date:</label>
                            <input type="date" id="coming_date" name="coming_date" class="form-control" value="<?= $movie['coming_date'] ?>">
                        </div>
                        <div class="col">
                            <label for="end_date" class="form-label">End Date:</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $movie['end_date'] ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="movie_duration" class="form-label">Duration (minutes):</label>
                        <input type="number" id="movie_duration" name="movie_duration" class="form-control" value="<?= $movie['movie_duration'] ?>" required>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
                <!-- Images -->
                <div class="card-edit-movie-admin p-4 shadow-sm mb-4">
                    <h5 class="mb-3">🖼️ Images</h5>
                    <div class="d-flex flex-wrap">
                        <?php foreach ($images as $img): ?>
                            <div class="position-relative me-2 mb-2">
                                <img src="<?= $img['image_path'] ?>" class="img-thumbnail" width="120">
                                <a href="delete_image.php?id=<?= $img['id'] ?>&movie_id=<?= $movie_id ?>" class="btn btn-sm delete-btn-movieEdit position-absolute top-0 end-0 m-1">x</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3">
                        <label for="images" class="form-label">Upload New Images:</label>
                        <input type="file" class="form-control" name="images[]" multiple>
                    </div>
                </div>

                <!-- Video -->
                <div class="card-edit-movie-admin p-4 shadow-sm mb-4">
                    <h5 class="mb-3">🎥 Video</h5>
                    <?php if (!empty($movie['video'])): ?>
                        <video width="100%" height="240" controls class="mb-2 rounded shadow-sm">
                            <source src="<?= htmlspecialchars($movie['video']) ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div>
                            <a href="delete_video.php?id=<?= $movie_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this video?');">Delete Video</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No video uploaded.</p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <label for="video" class="form-label">Upload New Video (MP4):</label>
                        <input type="file" class="form-control" name="video" accept=".mp4">
                    </div>
                </div>

                <!-- Genres -->
                <div class="card-edit-movie-admin p-4 shadow-sm">
                    <h5 class="mb-3">🎭 Genres</h5>
                    <select class="form-select select2" name="genre[]" multiple required>
                        <?php while ($genre = $genreResult->fetch_assoc()): ?>
                            <option value="<?= $genre['id'] ?>" <?= in_array($genre['id'], $selected_genres) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($genre['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="text-muted">Hold Ctrl (Windows) / Cmd (Mac) to select multiple</small>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success btn-lg px-4">💾 Update Movie</button>
        </div>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select genres",
            allowClear: true
        });
    });
</script>
</body>
</html>
