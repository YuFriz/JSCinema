<?php
global $conn;
require 'session_manager.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema - Repertoire</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="repertoires">

<!-- GÓRNA NAWIGACJA -->
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



<main class="container mt-4">
    <div class="repertoire-card p-4 shadow-lg rounded-4">
    <h1 class="repertoire-title">Movie Repertoires</h1>


    <!-- Pasek z datami i filtr gatunku -->
    <div class="repertoire-filter-bar">
    <div class="row">
            <div class="col-md-9 d-flex flex-wrap">
                <?php
                $weekDays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
                $today = time();

                for ($i = 0; $i < 7; $i++) {
                    $date = strtotime("+$i days", $today);
                    $dayName = $weekDays[date("w", $date)]; // Pobiera nazwę dnia
                    $dayNumber = date("j M", $date); // Pobiera numer dnia i miesiąc
                    echo "<button class='btn btn-danger mx-1 my-1 date-btn' data-date='" . date("Y-m-d", $date) . "'>$dayName <br> $dayNumber</button>";
                }
                ?>
            </div>
            <div class="col-md-3 text-end">
                <label for="genre" class="text-white me-2">Genre:</label>
                <select id="genre" class="form-select d-inline-block w-auto">
                    <option value="">All</option>
                    <?php
                    require 'db_connection.php';
                    $query = "SELECT id, name FROM genres ORDER BY name ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Sekcja filmów -->
    <div id="movies-list" class="row mt-3">
        <!-- Filmy będą ładowane dynamicznie -->
    </div>
</main>


<script>
    function calculateOffset(available, total) {
        const percent = available / total;
        const circumference = 2 * Math.PI * 20; // 2πr, r = 20
        return circumference * (1 - percent);
    }

    function getColor(available, total) {
        const percent = available / total;
        if (percent > 0.6) return "#7ED321";   // zielony
        if (percent > 0.3) return "#f5a623";   // pomarańczowy
        return "#d0021b";                      // czerwony
    }

    function getMovieIdFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('movie_id');
    }

    function loadMovies() {
        let selectedDate = document.querySelector('.date-btn.active')?.getAttribute('data-date') || document.querySelector('.date-btn')?.getAttribute('data-date');
        let selectedGenre = document.getElementById('genre').value;
        let movieId = getMovieIdFromURL();

        let url = `get_movies.php?date=${selectedDate}&genre=${selectedGenre}`;
        if (movieId) {
            url += `&movie_id=${movieId}`;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                let moviesList = document.getElementById('movies-list');
                moviesList.innerHTML = '';

                if (data.length > 0) {
                    // Sortowanie po czasie: od najwcześniejszego do najpóźniejszego
                    data.sort((a, b) => {
                        return a.start_time.localeCompare(b.start_time);
                    });

                    data.forEach(movie => {
                        let movieElement = document.createElement('div');
                        movieElement.classList.add('col-md-12', 'mb-4');

                        movieElement.innerHTML = `
            <div class="movie-modern-card d-flex flex-wrap align-items-center bg-white shadow-sm rounded-4 overflow-hidden p-3">
                <div class="movie-img col-md-3 text-center mb-3 mb-md-0">
                    <img src="${movie.img1}" class="img-fluid rounded-3" alt="${movie.name}">
                </div>
                <div class="movie-info col-md-9 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                        <h4 class="fw-bold text-dark mb-2 mb-md-0">${movie.name}</h4>
                        <span class="text-muted fs-5"><i class="bi bi-clock me-1"></i> ${movie.start_time}</span>
                    </div>
                    <p class="text-muted">${movie.description}</p>
                    <div class="row mt-2">
                        <div class="col-md-4"><strong>Auditorium:</strong> ${movie.auditorium}</div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="progress-ring me-2">
                                <svg width="50" height="50">
                                    <circle cx="25" cy="25" r="20" stroke="#eee"></circle>
                                    <circle cx="25" cy="25" r="20"
                                        stroke="${getColor(movie.available_seats, movie.total_seats)}"
                                        stroke-dasharray="126"
                                        stroke-dashoffset="${calculateOffset(movie.available_seats, movie.total_seats)}"></circle>
                                </svg>
                            </div>
                            <div>
                                <small class="text-muted">Available</small><br>
                                <strong>${movie.available_seats}</strong>
                            </div>
                        </div>

                    </div>
                    <div class="text-end mt-3">
                        <form action="check_login.php" method="get">
                            <input type="hidden" name="movie_id" value="${movie.id}">
                            <input type="hidden" name="screening_id" value="${movie.screening_id}">
                            <input type="hidden" name="movie_name" value="${movie.name}">
                            <input type="hidden" name="start_time" value="${movie.start_time}">
                            <button type="submit" class="btn btn-danger px-4">Reserve</button>
                        </form>
                    </div>
                </div>
            </div>
            `;
                        moviesList.appendChild(movieElement);
                    });
                } else {
                    moviesList.innerHTML = '<p class="text-white">No movies available for this selection.</p>';
                }
            })

                .catch(error => console.error('Error:', error));
    }


    // Obsługa kliknięcia w datę
    document.querySelectorAll('.date-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.date-btn').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            loadMovies();
        });
    });

    // Obsługa zmiany gatunku
    document.getElementById('genre').addEventListener('change', function() {
        loadMovies();
    });

    // Po załadowaniu strony: zaznacz dzisiejszy przycisk i załaduj filmy
    document.addEventListener("DOMContentLoaded", function () {
        const today = new Date().toISOString().split("T")[0]; // format: yyyy-mm-dd
        const buttons = document.querySelectorAll('.date-btn');

        buttons.forEach(btn => {
            if (btn.dataset.date === today) {
                btn.classList.add('active');
            }
        });

        loadMovies();
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
