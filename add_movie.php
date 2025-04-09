<?php
require 'session_manager.php';
require 'db_connection.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can add movies.");
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pobieranie danych z formularza
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $author = $conn->real_escape_string($_POST['author']);
    $movie_duration = $_POST['movie_duration'];
    $cast = $conn->real_escape_string($_POST['cast']);
    $status = $conn->real_escape_string($_POST['status']);
    $genres = isset($_POST['genre']) ? $_POST['genre'] : [];
    $coming_date = $_POST['coming_date'] ?: NULL;
    $end_date = $_POST['end_date'] ?: NULL;

    $stars = 0; // Domyślna wartość

    // Wstawienie filmu do tabeli movies
    $sql = "INSERT INTO movies (name, description, stars, author, movie_duration, plays, status, coming_date, end_date, created_at, updated_at)
            VALUES ('$name', '$description', '$stars', '$author', '$movie_duration', '$cast', '$status', '$coming_date', '$end_date', NOW(), NOW())";

    if ($conn->query($sql) === TRUE) {
        $movie_id = $conn->insert_id;
        echo "<div class='alert alert-success text-center'>New movie added with ID: $movie_id</div>";

        // Tworzenie folderu dla filmu
        $movie_folder = "movies/$movie_id";
        if (!file_exists($movie_folder) && !mkdir($movie_folder, 0777, true)) {
            echo "<div class='alert alert-danger text-center'>Error: Failed to create movie folder!</div>";
        }

// Obsługa przesyłania wielu obrazów
        if (!empty($_FILES['images']['name'][0])) {
            echo "<div class='alert alert-info text-center'>Processing multiple images...</div>";
            foreach ($_FILES['images']['name'] as $index => $filename) {
                if ($_FILES['images']['error'][$index] == 0) {
                    $file_name = "$movie_id-" . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", basename($filename));
                    $destination = "$movie_folder/$file_name";

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$index], $destination)) {
                        $image_path = "movies/$movie_id/$file_name";
                        $escaped_image_path = $conn->real_escape_string($image_path); // Zabezpieczenie wartości SQL

                        $sql_image = "INSERT INTO movie_images (movie_id, image_path) VALUES ('$movie_id', '$escaped_image_path')";
                        if ($conn->query($sql_image) === TRUE) {
                            echo "<div class='alert alert-success text-center'>Image uploaded: $image_path</div>";
                        } else {
                            echo "<div class='alert alert-danger text-center'>Database error: " . $conn->error . "</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger text-center'>Error: File upload failed for $filename!</div>";
                    }
                } else {
                    echo "<div class='alert alert-warning text-center'>File upload error for $filename!</div>";
                }
            }
        }


        // Obsługa wideo
        if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
            $video_name = "$movie_id-" . preg_replace("/[^a-zA-Z0-9\-_\.]/", "_", basename($_FILES['video']['name'])); // Usuwanie znaków specjalnych
            $video_path = "$movie_folder/$video_name";

            if (move_uploaded_file($_FILES['video']['tmp_name'], $video_path)) {
                $escaped_video_path = $conn->real_escape_string($video_path); // Zabezpieczenie wartości SQL
                $sql_update_video = "UPDATE movies SET video = '$escaped_video_path' WHERE id = '$movie_id'";
                $conn->query($sql_update_video);
            } else {
                echo "<div class='alert alert-danger text-center'>Error: Video upload failed!</div>";
            }
        }


        // Zapisanie gatunków do tabeli movie_genres
        if (!empty($genres)) {
            $genre_values = [];
            foreach ($genres as $genre_id) {
                $genre_values[] = "('$movie_id', '$genre_id')";
            }

            if (!empty($genre_values)) {
                $sql_genre = "INSERT INTO movie_genres (movie_id, genre_id) VALUES " . implode(', ', $genre_values);
                $conn->query($sql_genre);
            }
        }
    } else {
        echo "<div class='alert alert-danger text-center'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Movie - JSCinema</title>

    <!--bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- select2-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Nawigacja -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="movie_admin.php" class="btn btn-outline-light">Back to Movie List</a>
    </div>
</nav>

<!-- Główna zawartość -->
<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card add-movie-card shadow-lg p-4">
            <h2 class="text-center mb-4">Add New Movie</h2>
                <form action="add_movie.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="name" class="form-label">Movie Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="author" class="form-label">Author:</label>
                        <input type="text" class="form-control" name="author" required>
                    </div>

                    <div class="mb-3">
                        <label for="movie_duration" class="form-label">Movie Duration (minutes):</label>
                        <input type="number" class="form-control" name="movie_duration" required>
                    </div>

                    <div class="mb-3">
                        <label for="cast" class="form-label">Cast:</label>
                        <textarea class="form-control" name="cast" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="genre" class="form-label">Genres:</label>
                        <select class="form-select select2" name="genre[]" multiple required>
                            <?php
                            // Pobieranie listy gatunków z bazy
                            $genreSql = "SELECT * FROM genres";
                            $genreResult = $conn->query($genreSql);

                            while ($genre = $genreResult->fetch_assoc()) {
                                echo "<option value='" . $genre['id'] . "'>" . htmlspecialchars($genre['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) / Command (Mac) to select multiple genres.</small>
                    </div>



                    <div class="mb-3">
                        <label for="status" class="form-label">Status:</label>
                        <select class="form-select" name="status">
                            <option value="already showing">Already showing</option>
                            <option value="soon in cinema">Soon in cinema</option>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="images" class="form-label">Movie Images:</label>
                        <input type="file" class="form-control" name="images[]" accept="image/*" multiple>
                        <small class="text-muted">You can upload multiple images.</small>
                    </div>

                    <div class="mb-3">
                        <label for="video" class="form-label">Video:</label>
                        <input type="file" class="form-control" name="video" accept="video/*" required>
                    </div>


                    <div class="mb-3">
                        <label for="coming_date" class="form-label">Coming Date:</label>
                        <input type="date" class="form-control" name="coming_date">
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date:</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Add Movie</button>
                </form>
            </div>
        </div>
    </div>
</main>


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