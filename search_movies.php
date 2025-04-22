<?php
require 'session_manager.php';
require 'db_connection.php';

if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode([]);
    exit;
}

$query = '%' . $_GET['query'] . '%';

$sql = "
    SELECT id, name, 
           (SELECT image_path FROM movie_images 
            WHERE movie_images.movie_id = movies.id 
            ORDER BY movie_images.id LIMIT 1) AS image_path
    FROM movies 
    WHERE name LIKE ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["error" => "Błąd przygotowania zapytania: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $query);

if (!$stmt->execute()) {
    echo json_encode(["error" => "Błąd wykonania zapytania: " . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

$movies = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($movies ?: []);
?>
