<?php
require 'session_manager.php';

$host = "localhost";
$dbname = "cinemajs";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function fetchMovies($pdo, $status) {
    $sql = "SELECT movies.id, movies.name, 
                   (SELECT image_path FROM movie_images 
                    WHERE movie_images.movie_id = movies.id 
                    ORDER BY movie_images.id LIMIT 1) AS image_path
            FROM movies 
            WHERE movies.status = :status";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['status' => $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
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


<div class="banner">
    <?php include 'banner.php'; ?>
</div>

<main>
    <?php foreach (["already showing" => "Already Showing", "soon in cinema" => "Soon in Cinema"] as $status => $title): ?>
        <section id="<?php echo str_replace(' ', '_', strtolower($status)); ?>">
            <div class="container">
                <h2 class="text-center my-4"><?php echo $title; ?></h2>
                <div class="row">
                    <?php $movies = fetchMovies($pdo, $status);
                    if ($movies):
                        foreach ($movies as $movie): ?>
                            <div class='col-md-3 mb-4'>
                                <div class='card'>
                                    <img src='<?php echo htmlspecialchars($movie['image_path']); ?>' class='card-img-top' alt='<?php echo htmlspecialchars($movie['name']); ?>'>
                                    <div class='card-body text-center'>
                                        <h5 class='card-title'><?php echo htmlspecialchars($movie['name']); ?></h5>
                                        <a href='movie.php?id=<?php echo $movie['id']; ?>' class='btn btn-primary'>View Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                    else:
                        echo "<p class='text-center'>No movies available.</p>";
                    endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<footer>
    <p>&copy; JSCinema. All rights reserved.</p>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>