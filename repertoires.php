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
    <link rel="stylesheet" href="style.css">
</head>
<body>

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

<main class="container mt-4">
    <h1>Movie Repertoire</h1>

    <!-- Pasek z datami i filtr gatunku -->
    <div class="bg-dark text-white p-3 rounded">
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
                    data.forEach(movie => {
                        let movieElement = document.createElement('div');
                        movieElement.classList.add('col-md-12', 'mb-4');

                        movieElement.innerHTML = `
                        <div class="card p-2">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="${movie.img1}" class="img-fluid rounded-start" alt="${movie.name}">
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body">
                                        <h5 class="card-title">${movie.name}</h5>
                                        <p class="card-text">${movie.description}</p>
                                        <p><strong>Time:</strong> ${movie.start_time}</p>
                                        <p><strong>Auditorium:</strong> ${movie.auditorium}</p>
                                        <p><strong>Seats:</strong> ${movie.available_seats} / ${movie.total_seats}</p>
                                        <form action="check_login.php" method="get">
                                            <input type="hidden" name="movie_id" value="${movie.id}">
                                            <input type="hidden" name="screening_id" value="${movie.screening_id}">
                                            <input type="hidden" name="movie_name" value="${movie.name}">
                                            <input type="hidden" name="start_time" value="${movie.start_time}">
                                            <button type="submit" class="btn btn-primary">Reserve</button>
                                        </form>
                                    </div>
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
</body>
</html>
