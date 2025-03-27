<?php
require 'session_manager.php';
require 'db_connection.php';

// Pobranie ID filmu z URL
if (isset($_GET['id'])) {
    $movieId = (int) $_GET['id'];

    // Zapytanie SQL do pobrania szczegółów filmu i gatunków
    $sql = "SELECT m.*, 
                   IFNULL(GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', '), 'Brak gatunku') AS genre
            FROM movies m
            LEFT JOIN movie_genres mg ON m.id = mg.movie_id
            LEFT JOIN genres g ON mg.genre_id = g.id
            WHERE m.id = ?
            GROUP BY m.id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();

    $movie = $result->fetch_assoc();
}


// Pobranie zdjęć filmu
$imageSql = "SELECT image_path FROM movie_images WHERE movie_id = ?";
$imageStmt = $conn->prepare($imageSql);
$imageStmt->bind_param("i", $movieId);
$imageStmt->execute();
$imageResult = $imageStmt->get_result();

$movieImages = [];
while ($row = $imageResult->fetch_assoc()) {
    $movieImages[] = $row['image_path'];
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema - <?= htmlspecialchars($movie['name'] ?? 'Film') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="aboutCinema.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="repertoires.php">Repertoires</a></li>
                <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
                <li class="nav-item">
                    <form class="d-flex position-relative">
                        <input class="form-control" id="myInput" type="text" placeholder="Search movies..." autocomplete="off">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                        <div id="search-results" class="position-absolute w-100 bg-white shadow rounded"></div>
                    </form>
                </li>
                <li class="nav-item">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 ms-2" href="profile.php" title="Profile" style="width: 42px; height: 42px;">
                            <i class="bi bi-person-circle fs-4"></i>
                        </a>
                    <?php else: ?>
                        <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 ms-2" href="register_login.php" title="Login/Register" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-arrow-in-right fs-4"></i>
                        </a>
                    <?php endif; ?>
                </li>

            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><?= htmlspecialchars($movie['name'] ?? 'Unknown Movie') ?></h2>
        <span class="fs-4 text-warning">&#9733; <?= number_format($movie['stars'] ?? 0, 2) ?>/5</span>
    </div>

    <div class="row mt-4">
        <div class="col-md-8">
            <video class="w-100 border rounded shadow" controls>
                <source src="Movies/<?= htmlspecialchars($movie['id']) ?>/<?= htmlspecialchars($movie['id']) ?>_video.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <div class="col-md-4 d-flex flex-column justify-content-between">
            <div class="border rounded p-3 shadow-sm">
                <p><strong>Film genre:</strong> <?= htmlspecialchars($movie['genre'] ?? 'Unknown') ?></p>
                <p><strong>Movie duration:</strong> <?= htmlspecialchars($movie['movie_duration'] ?? 'N/A') ?>h</p>
                <p><strong>Director:</strong> <?= htmlspecialchars($movie['author'] ?? 'Unknown') ?></p>
                <p><strong>Plays:</strong> <?= htmlspecialchars($movie['plays'] ?? 'Unknown') ?></p>
            </div>
            <a href="repertoires.php?movie_id=<?= $movieId ?>" class="btn btn-primary w-100 mt-3 py-2 shadow">Choose a session</a>


        </div>
    </div>
    <?php if (!empty($movieImages)): ?>
        <div class="row mt-4">
            <div class="col-md-12 border rounded p-3 shadow-sm">
                <h4>Movie Images</h4>
                <div class="d-flex flex-wrap">
                    <?php foreach ($movieImages as $image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" class="img-thumbnail m-2 movie-image"
                             style="width: 150px; height: auto; cursor: pointer;"
                             data-bs-toggle="modal" data-bs-target="#imageModal"
                             data-image="<?= htmlspecialchars($image) ?>">
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 🔹 Modal do powiększania zdjęć -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Użycie modal-lg dla większego rozmiaru -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Movie Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" class="img-fluid rounded shadow modal-img">
                </div>
            </div>
        </div>
    </div>



    <div class="row mt-4">
        <div class="col-md-12 border rounded p-3 shadow-sm">
            <h4>About the movie</h4>
            <p><?= htmlspecialchars($movie['description'] ?? 'No description available.') ?></p>
        </div>
    </div>



</div>



<!--POWIEKSZANIE ZDJECI -->
<script>
    document.querySelectorAll('.movie-image').forEach(img => {
        img.addEventListener('click', function () {
            let imageUrl = this.getAttribute('data-image');
            document.getElementById('modalImage').setAttribute('src', imageUrl);
        });
    });
</script>



<!--SEARCH-->
<script>
    $(document).ready(function() {
        $("#myInput").on("input", function() {
            let query = $(this).val();
            if (query.length >= 2) {
                $.ajax({
                    url: "search_movies.php",
                    method: "GET",
                    data: { query: query },
                    dataType: "json",
                    success: function(response) {
                        let resultsContainer = $("#search-results");
                        resultsContainer.empty();

                        if (response.length > 0) {
                            response.forEach(movie => {
                                resultsContainer.append(`
                                    <div class='search-item p-2 border-bottom d-flex align-items-center' data-url="movie.php?id=${movie.id}" style="cursor: pointer;">
                                        <img src="${movie.image_path}" alt="${movie.name}" class="me-2 rounded" style="width: 50px; height: 50px;">
                                        <span class="text-dark">${movie.name}</span>
                                    </div>
                                `);

                            });

                            // Dodanie event listenera dla kliknięcia na wynik
                            $(".search-item").on("click", function() {
                                window.location.href = $(this).attr("data-url");
                            });
                        } else {
                            resultsContainer.append("<div class='search-item p-2 text-muted'>No results found</div>");
                        }
                    }
                });
            } else {
                $("#search-results").empty();
            }
        });

        // Ukryj wyniki po kliknięciu poza pole wyszukiwania
        $(document).click(function(event) {
            if (!$(event.target).closest("#myInput, #search-results").length) {
                $("#search-results").empty();
            }
        });
    });

</script>





<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
