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
    die(json_encode(["error" => "Connection failed: " . $e->getMessage()]));
}

if (!isset($_GET['query']) || empty($_GET['query'])) {
    echo json_encode([]);
    exit;
}

$query = '%' . $_GET['query'] . '%';

$sql = "SELECT id, name, 
               (SELECT image_path FROM movie_images WHERE movie_images.movie_id = movies.id ORDER BY movie_images.id LIMIT 1) AS image_path
        FROM movies 
        WHERE name LIKE :query";

$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => $query]);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$movies) {
    echo json_encode([]);
} else {
    echo json_encode($movies);
}
?>
