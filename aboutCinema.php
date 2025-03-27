<?php
require 'session_manager.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Our Cinema Theater</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!--GORNIA NAWIGACJA -->

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



<!-- Główna zawartość -->
<main class="container my-5">
    <h1 class="text-center mb-4">About Our Cinema Theater</h1>

    <section class="row">
        <div class="col-md-6 d-flex align-items-center">
            <p>Welcome to JSCinema, the premier destination for movie enthusiasts and theater lovers alike! Our theater offers a unique experience with the latest blockbusters, timeless classics, and exclusive events.</p>
        </div>
        <div class="col-md-6 text-center">
            <img src="about/image1.jpg" class="img-fluid rounded shadow" alt="Cinema Image 1">
        </div>
    </section>

    <section class="row mt-4">
        <div class="col-md-6 order-md-2 d-flex align-items-center">
            <p>Founded in 2025, JSCinema has quickly become a beloved community hub in Vilnius, providing a cozy and modern environment for all movie-goers. Whether you're here for a night out with friends, a family outing, or a special occasion, we strive to make every visit memorable.</p>
        </div>
        <div class="col-md-6 order-md-1 text-center">
            <img src="about/image2.jpg" class="img-fluid rounded shadow" alt="Cinema Image 2">
        </div>
    </section>

    <section class="row mt-4">
        <div class="col-md-6 d-flex align-items-center">
            <p>Our state-of-the-art screening rooms are equipped with the latest technology, ensuring that you enjoy films in the highest quality possible. With a passionate team dedicated to delivering top-notch service, JSCinema is the perfect place to indulge in your love for cinema.</p>
        </div>
        <div class="col-md-6 text-center">
            <img src="about/image3.jpg" class="img-fluid rounded shadow" alt="Cinema Image 3">
        </div>
    </section>

    <section class="row mt-4">
        <div class="col-md-6 order-md-2 d-flex align-items-center">
            <p>Thank you for choosing JSCinema, and we look forward to seeing you soon!</p>
        </div>
        <div class="col-md-6 order-md-1 text-center">
            <img src="about/image4.jpg" class="img-fluid rounded shadow" alt="Cinema Image 4">
        </div>
    </section>
</main>

<!-- Stopka -->
<footer class="bg-dark text-light text-center py-3">
    <p>&copy; 2025 JSCinema. All rights reserved.</p>
</footer>





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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
