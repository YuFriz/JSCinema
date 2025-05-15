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


// Domyślnie ukryjemy filmy zakończone, chyba że podano konkretny status
if (empty($statusFilter)) {
    $conditions[] = "movies.status != 'screening ended'";
}

// Dodaj warunki do zapytania
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
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema - Movies</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
                <li class="nav-item d-flex align-items-center ms-lg-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="d-flex align-items-center gap-2">
                            <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 icon-log"
                               href="profile.php" title="Profile" style="width: 42px; height: 42px;">
                                <i class="bi bi-person-circle fs-4"></i>
                            </a>
                            <form action="logout.php" method="post">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 icon-log"
                           href="register_login.php" title="Login/Register" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-arrow-in-right fs-4"></i>
                        </a>
                    <?php endif; ?>
                </li>

            </ul>
        </div>
    </div>
</nav>


<!-- 🔹 Filtry -->
<div class="container my-4">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="movies.php" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center py-2 shadow-sm filter-btn">
                <i class="bi bi-collection-play me-2"></i> All Movies
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="movies.php?status=showing" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center py-2 shadow-sm filter-btn">
                <i class="bi bi-film me-2"></i> Now Showing
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="movies.php?status=soon" class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center py-2 shadow-sm filter-btn">
                <i class="bi bi-clock-history me-2"></i> Coming Soon
            </a>
        </div>
        <div class="col-6 col-md-3">
            <button class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-center py-2 shadow-sm filter-btn" id="genreButton">
                <i class="bi bi-tags me-2"></i> Filter
            </button>
        </div>
    </div>



    <!-- 🔹 Panel filtrów -->
    <div class="row mt-3" id="genreTable" style="display: none;">
        <div class="col-md-8">
            <div class="border rounded p-3 shadow-sm bg-light">
                <h5>Genres</h5>
                <div class="row">
                    <?php
                    $genreSql = "SELECT * FROM genres";
                    $genreResult = $conn->query($genreSql);

                    if ($genreResult->num_rows > 0) {
                        while ($genre = $genreResult->fetch_assoc()) {
                            $genreName = htmlspecialchars($genre['name']);
                            $genreId = (int)$genre['id'];
                            echo "
                    <div class='col-md-4'>
                        <div class='form-check'>
                            <input class='form-check-input genre-checkbox' type='checkbox' value='$genreName' id='genre$genreId' name='genres[]'>
                            <label class='form-check-label' for='genre$genreId'>$genreName</label>
                        </div>
                    </div>";
                        }
                    } else {
                        echo "<p class='text-center'>No genres found.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Gwiazdki po prawej -->
        <div class="col-md-4">
            <div class="border rounded p-3 shadow-sm bg-light">
                <h5>Filter by Stars</h5>
                <div class="row">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="col-12">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input star-checkbox me-2" type="checkbox" value="<?= $i ?>" id="star<?= $i ?>" name="stars[]">
                                <label class="form-check-label" for="star<?= $i ?>"><?= str_repeat("⭐", $i) ?></label>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>


        <!-- 🔹 Przycisk Search -->
        <div class="text-center my-3">
            <button id="filterSearchBtn" class="btn btn-outline-dark d-flex align-items-center justify-content-center py-2 shadow-sm">
                <i class="bi bi-search me-2"></i> Search
            </button>
        </div>
    </div>



    <!-- 🔹 Lista filmów -->
    <div class="container my-4">
        <h2 class="text-center mb-4">Movies List</h2>
        <div class="row movies-list">
            <?php
            if (!$result) {
                die("<p class='text-danger text-center'>❌ Błąd zapytania SQL: " . $conn->error . "</p>");
            }

            if ($result->num_rows > 0) {
                while ($movie = $result->fetch_assoc()) {
                    $comingSoonBadge = ($movie['status'] === 'soon in cinema') ? "<div class='coming-soon-banner'>Coming Soon</div>" : "";
                    echo "
            <div class='col-md-4 mb-4'>
                <div class='card position-relative movie-card allmovie-card' onclick=\"window.location.href='movie.php?id={$movie['id']}'\">
                    <img src='{$movie['img1']}' class='card-img-top' alt='{$movie['name']}' style='height: 300px; object-fit: cover;'>
                    <div class='allmovie-card text-center'>
                        <h5 class='card-allmovie-title'>{$movie['name']}</h5>
                        <p><strong>Genres:</strong> " . htmlspecialchars($movie['genres']) . "</p>
                        <p><strong>Duration:</strong> {$movie['movie_duration']} min</p>
                        <p><strong>Stars:</strong> " . str_repeat('⭐', max(0, (int) $movie['stars'])) . "</p>
                    </div>
                    $comingSoonBadge
                </div>
            </div>";
                }
            }
            ?>
        </div>
    </div>






<!-- 🔹 JavaScript do rozwijania gatunków -->
<script>
    document.getElementById("genreButton").addEventListener("click", function () {
        const genreTable = document.getElementById("genreTable");
        genreTable.style.display = genreTable.style.display === "none" ? "flex" : "none";
    });
</script>

    <script>
        function getCheckedValues(selector) {
            return Array.from(document.querySelectorAll(selector + ':checked')).map(cb => cb.value);
        }

        function loadMovies() {
            const selectedGenres = getCheckedValues('.genre-checkbox');
            const selectedStars = getCheckedValues('.star-checkbox');

            console.log("Genres selected:", selectedGenres);
            console.log("Stars selected:", selectedStars);

            $.ajax({
                url: "filter_movies.php",
                type: "POST",
                data: {
                    genres: JSON.stringify(selectedGenres),
                    stars: JSON.stringify(selectedStars)
                },
                dataType: "json",
                success: function (data) {
                    console.log("Response from server:", data);

                    let moviesContainer = $(".row");
                    moviesContainer.empty();

                    if (data.length > 0) {
                        data.forEach(movie => {
                            let starsDisplay = '⭐'.repeat(movie.stars);

                            moviesContainer.append(`
                        <div class='col-md-4 mb-4'>
                            <div class='card position-relative movie-card allmovie-card' onclick=\"window.location.href='movie.php?id={$movie['id']}'\">
                                <img src="${movie.img1}" class='card-img-top' alt="${movie.name}" style='height: 300px; object-fit: cover;'>
                                 <div class='allmovie-card text-center'>
                                    <h5 class='card-allmovie-title'>${movie.name}</h5>
                                    <p><strong>Genres:</strong> ${movie.genres}</p>
                                    <p><strong>Duration:</strong> ${movie.movie_duration} min</p>
                                    <p><strong>Stars:</strong> ${starsDisplay}</p>
                                </div>
                                ${movie.status === 'soon in cinema' ? "<div class='coming-soon-banner'>Coming Soon</div>" : ""}
                            </div>
                        </div>
                    `);
                        });
                    } else {
                        moviesContainer.html("<p class='text-center text-danger'>⚠️ No movies found matching filters.</p>");
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX error:", status, error);
                }
            });
        }

        // 🔹 Event listener dla przycisku "Search"
        $(document).ready(function () {
            $("#filterSearchBtn").on("click", function () {
                const selectedGenres = $(".genre-checkbox:checked").map(function () { return this.value; }).get();
                const selectedStars = $(".star-checkbox:checked").map(function () { return this.value; }).get();

                $.ajax({
                    url: "filter_movies.php",
                    type: "POST",
                    data: {
                        genres: selectedGenres,
                        stars: selectedStars
                    },
                    dataType: "json",
                    success: function (data) {
                        let moviesContainer = $(".row.movies-list");
                        moviesContainer.empty();

                        if (data.length > 0) {
                            data.forEach(movie => {
                                let starsDisplay = '⭐'.repeat(movie.stars);
                                moviesContainer.append(`
                            <div class='col-md-4 mb-4'>
                                <div class='card position-relative movie-card allmovie-card' onclick=\"window.location.href='movie.php?id={$movie['id']}'\">
                                    <img src="${movie.img1}" class='card-img-top' alt="${movie.name}" style='height: 300px; object-fit: cover;'>
                                     <div class='allmovie-card text-center'>
                                        <h5 class='card-allmovie-title'>${movie.name}</h5>
                                        <p><strong>Genres:</strong> ${movie.genres}</p>
                                        <p><strong>Duration:</strong> ${movie.movie_duration} min</p>
                                        <p><strong>Stars:</strong> ${starsDisplay}</p>
                                    </div>
                                    ${movie.status === 'soon in cinema' ? "<div class='coming-soon-banner'>Coming Soon</div>" : ""}
                                </div>
                            </div>
                        `);
                            });
                        } else {
                            moviesContainer.html("<p class='text-center text-danger'>⚠️ No movies found matching filters.</p>");
                        }
                    },
                    error: function () {
                        console.error("Error loading movies.");
                    }
                });
            });
        });


    </script






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
