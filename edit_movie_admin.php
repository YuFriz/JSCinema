<?php
require 'db_connection.php';
require 'session.php';


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



// Sprawdzenie ID filmu
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid movie ID!");
}

$movie_id = intval($_GET['id']);

// Pobranie szczegółów filmu
$stmt = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Movie not found!");
}

$movie = $result->fetch_assoc();
$stmt->close();

// Pobranie obrazów powiązanych z filmem
$image_stmt = $conn->prepare("SELECT id, image_path FROM movie_images WHERE movie_id = ?");
$image_stmt->bind_param("i", $movie_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$images = $image_result->fetch_all(MYSQLI_ASSOC);
$image_stmt->close();

// Pobranie gatunków przypisanych do filmu
$genre_stmt = $conn->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
$genre_stmt->bind_param("i", $movie_id);
$genre_stmt->execute();
$genre_result = $genre_stmt->get_result();
$selected_genres = [];
while ($row = $genre_result->fetch_assoc()) {
    $selected_genres[] = $row['genre_id'];
}
$genre_stmt->close();

// Pobranie listy wszystkich gatunków
$genreSql = "SELECT * FROM genres";
$genreResult = $conn->query($genreSql);




// Obsługa edycji filmu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $stars = $_POST['stars'];
    $author = $_POST['author'];
    $video = $_POST['video'];
    $movie_duration = intval($_POST['movie_duration']);
    $plays = $_POST['plays'];
    $status = $_POST['status'];
    $coming_date = $_POST['coming_date'];
    $end_date = $_POST['end_date'];
    $genres = isset($_POST['genre']) ? $_POST['genre'] : [];

    // Aktualizacja informacji o filmie
    $update_stmt = $conn->prepare("UPDATE movies SET name = ?, description = ?, stars = ?, author = ?, video = ?, movie_duration = ?, plays = ?, status = ?, coming_date = ?, end_date = ? WHERE id = ?");
    $update_stmt->bind_param("sssssissssi", $name, $description, $stars, $author, $video, $movie_duration, $plays, $status, $coming_date, $end_date, $movie_id);

    // Aktualizacja gatunków
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

    // Obsługa przesyłania nowych obrazów
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $index => $filename) {
            if ($_FILES['images']['error'][$index] == 0) {
                $file_name = "$movie_id-" . basename($filename);
                $destination = "Movies/$movie_id/$file_name";

                if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $destination)) {
                    $image_path = "Movies/$movie_id/$file_name";
                    $sql_image = "INSERT INTO movie_images (movie_id, image_path) VALUES ('$movie_id', '$image_path')";
                    $conn->query($sql_image);
                }
            }
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

<!-- Edit Movie Form -->
<div class="container mt-4">
    <h2 class="text-center">Edit Movie</h2>
    <form method="POST" class="mt-3" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Movie Name:</label>
            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($movie['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($movie['description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="stars" class="form-label">Stars:</label>
            <input type="text" id="stars" name="stars" class="form-control" value="<?php echo htmlspecialchars($movie['stars']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Author:</label>
            <input type="text" id="author" name="author" class="form-control" value="<?php echo htmlspecialchars($movie['author']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Images:</label>
            <div class="d-flex flex-wrap">
                <?php foreach ($images as $img): ?>
                    <div class="m-2">
                        <img src="<?php echo $img['image_path']; ?>" class="img-thumbnail" width="150">
                        <br>
                        <a href="delete_image.php?id=<?php echo $img['id']; ?>&movie_id=<?php echo $movie_id; ?>" class="btn btn-sm btn-danger mt-1">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="images" class="form-label">Upload New Images:</label>
            <input type="file" class="form-control" name="images[]" multiple>
        </div>

        <div class="mb-3">
            <label for="video" class="form-label">Video URL:</label>
            <input type="text" id="video" name="video" class="form-control" value="<?php echo $movie['video']; ?>">
        </div>

        <div class="mb-3">
            <label for="genre" class="form-label">Genres:</label>
            <select class="form-select select2" name="genre[]" multiple required>
                <?php while ($genre = $genreResult->fetch_assoc()): ?>
                    <option value="<?php echo $genre['id']; ?>" <?php echo in_array($genre['id'], $selected_genres) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($genre['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <small class="text-muted">Hold Ctrl (Windows) / Command (Mac) to select multiple genres.</small>
        </div>

        <div class="mb-3">
            <label for="movie_duration" class="form-label">Movie Duration (minutes):</label>
            <input type="number" id="movie_duration" name="movie_duration" class="form-control" value="<?php echo $movie['movie_duration']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="plays" class="form-label">Cast:</label>
            <input type="text" id="plays" name="plays" class="form-control" value="<?php echo htmlspecialchars($movie['plays']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status:</label>
            <select class="form-select" name="status">
                <option value="already showing" <?php echo ($movie['status'] == 'already showing') ? 'selected' : ''; ?>>Already showing</option>
                <option value="soon in cinema" <?php echo ($movie['status'] == 'soon in cinema') ? 'selected' : ''; ?>>Soon in cinema</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="coming_date" class="form-label">Coming Date:</label>
            <input type="date" id="coming_date" name="coming_date" class="form-control" value="<?php echo $movie['coming_date']; ?>">
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo $movie['end_date']; ?>">
        </div>

        <button type="submit" class="btn btn-success">Update Movie</button>
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
