<?php
global $conn;
require 'session_manager.php'; // Zarządzanie sesją
require 'db_connection.php'; // Połączenie z bazą danych

// Pobranie statusu z URL, jeśli istnieje
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$genreFilter = isset($_GET['genre']) ? $_GET['genre'] : '';

// Pobranie filmów i ich gatunków z bazy
$sql = "
    SELECT 
        movies.*, 
        (SELECT image_path FROM movie_images 
         WHERE movie_images.movie_id = movies.id 
         ORDER BY movie_images.id LIMIT 1) AS img1, 
        IFNULL(GROUP_CONCAT(DISTINCT genres.name ORDER BY genres.name SEPARATOR ', '), 'No Genre') AS genres
    FROM movies
    LEFT JOIN movie_genres ON movies.id = movie_genres.movie_id
    LEFT JOIN genres ON movie_genres.genre_id = genres.id";


// Warunki dla statusu i gatunku
$conditions = [];

// Filtrowanie po statusie (jeśli podany)
if ($statusFilter === 'showing') {
    $conditions[] = "movies.status = 'already showing'";
} elseif ($statusFilter === 'soon') {
    $conditions[] = "movies.status = 'soon in cinema'";
}

// Jeśli wybrano gatunek, filtrujemy po nim
if (!empty($genreFilter)) {
    $conditions[] = "genres.name = '" . $conn->real_escape_string($genreFilter) . "'";
}

// Jeśli istnieją warunki, dodajemy je do zapytania
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Grupowanie wyników
$sql .= " GROUP BY movies.id";

// Wykonanie zapytania
$result = $conn->query($sql);

if (!$result) {
    die("<p class='text-danger text-center'>❌ Błąd zapytania SQL: " . $conn->error . "</p>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema - Movies</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- 🔹 Górna nawigacja -->
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
                        <a class="nav-link" href="profile.php">Profile</a>
                    <?php else: ?>
                        <a class="nav-link" href="register_login.php">Login/Register</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- 🔹 Filtry -->
<div class="container my-4">
    <div class="row">
        <div class="col-md-3">
            <a href="movies.php" class="btn all-movies-btn w-100">All Movies</a>
        </div>
        <div class="col-md-3">
            <a href="movies.php?status=showing" class="btn showing-movies-btn w-100">Currently Showing</a>
        </div>
        <div class="col-md-3">
            <a href="movies.php?status=soon" class="btn coming-soon-btn w-100">Coming Soon</a>
        </div>
        <div class="col-md-3">
            <button class="btn genre-btn w-100" id="genreButton">Choose Genre</button>
        </div>
    </div>
</div>


<!-- 🔹 Dropdown z gatunkami -->
<div class="container my-3" id="genreTable" style="display: none;">
    <h3 class="text-center">Choose Genre</h3>
    <div class="row">
        <?php
        $genreSql = "SELECT * FROM genres";
        $genreResult = $conn->query($genreSql);

        if ($genreResult->num_rows > 0) {
            while ($genre = $genreResult->fetch_assoc()) {
                $genreName = htmlspecialchars($genre['name']);
                echo "<div class='col-md-3'><a href='movies.php?genre=" . urlencode($genreName) . "' class='btn btn-outline-dark w-100 my-1'>$genreName</a></div>";
            }
        } else {
            echo "<p class='text-center'>No genres found.</p>";
        }
        ?>
    </div>
</div>

<!-- 🔹 Lista filmów -->
<div class="container my-4">
    <h2 class="text-center mb-4">Movies List</h2>
    <div class="row">
        <?php
        if (!$result) {
            die("<p class='text-danger text-center'>❌ Błąd zapytania SQL: " . $conn->error . "</p>");
        }

        if ($result->num_rows > 0) {
            while ($movie = $result->fetch_assoc()) {
                echo "
        <div class='col-md-4 mb-4'>
            <div class='card'>
                <img src='{$movie['img1']}' class='card-img-top' alt='{$movie['name']}' style='height: 300px; object-fit: cover;'>
                <div class='card-body text-center'>
                    <h5 class='card-title'>{$movie['name']}</h5>
                    <p class='text-muted'><strong>Genres:</strong> " . htmlspecialchars($movie['genres']) . "</p>
                    <p><strong>Duration:</strong> {$movie['movie_duration']} min</p>
                    <p><strong>Stars:</strong> " . str_repeat('⭐', max(0, (int) $movie['stars'])) . "</p>
                    <a href='movie.php?id={$movie['id']}' class='btn btn-primary'>View Details</a>
                </div>
            </div>
        </div>";
            }
        } else {
            echo "<p class='text-center text-danger'>⚠️ Brak filmów w bazie lub błąd zapytania.</p>";
        }

        ?>
    </div>
</div>


<!-- 🔹 JavaScript do rozwijania gatunków -->
<script>
    document.getElementById("genreButton").addEventListener("click", function () {
        const genreTable = document.getElementById("genreTable");
        genreTable.style.display = genreTable.style.display === "none" ? "block" : "none";
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

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
