<?php
require 'db_connection.php';

$genres = isset($_POST['genres']) ? $_POST['genres'] : [];
$stars = isset($_POST['stars']) ? $_POST['stars'] : [];

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

// Warunki filtrowania
$conditions = [];

if (!empty($genres)) {
    $genrePlaceholders = implode("', '", array_map([$conn, 'real_escape_string'], $genres));
    $conditions[] = "genres.name IN ('$genrePlaceholders')";
}

if (!empty($stars)) {
    $starPlaceholders = implode(",", array_map('intval', $stars));
    $conditions[] = "movies.stars IN ($starPlaceholders)";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY movies.id";

$result = $conn->query($sql);
$movies = [];

if ($result->num_rows > 0) {
    while ($movie = $result->fetch_assoc()) {
        $movies[] = [
            'id' => $movie['id'],
            'name' => $movie['name'],
            'genres' => htmlspecialchars($movie['genres']),
            'movie_duration' => $movie['movie_duration'],
            'stars' => (int) $movie['stars'],
            'status' => $movie['status'],
            'img1' => $movie['img1']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($movies);
?>
