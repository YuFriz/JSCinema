<?php
if (isset($_GET['date'])) {
    $date = $_GET['date'];
    $genre = isset($_GET['genre']) && $_GET['genre'] !== '' ? $_GET['genre'] : null;
    $movieId = isset($_GET['movie_id']) && $_GET['movie_id'] !== '' ? (int) $_GET['movie_id'] : null;

    $conn = new mysqli("localhost", "root", "", "cinemajs");

    if ($conn->connect_error) {
        die("Połączenie nieudane: " . $conn->connect_error);
    }

    $query = "
        SELECT DISTINCT 
            m.id, 
            m.name, 
            m.description, 
            (SELECT image_path FROM movie_images WHERE movie_id = m.id LIMIT 1) AS img1,
            s.start_time, 
            a.name AS auditorium, 
            s.id AS screening_id,
            (SELECT COUNT(*) FROM seats WHERE auditorium_id = s.auditorium_id) AS total_seats,
            (SELECT COUNT(*) FROM purchased_tickets WHERE screening_id = s.id) AS occupied_seats
        FROM movies m
        JOIN screenings s ON m.id = s.movie_id
        JOIN auditoriums a ON s.auditorium_id = a.id
        LEFT JOIN movie_genres mg ON m.id = mg.movie_id
        WHERE s.screening_date = ?";

    $params = [$date];
    $param_types = "s";

    if (!empty($genre)) {
        $query .= " AND mg.genre_id = ?";
        $params[] = $genre;
        $param_types .= "i";
    }

    if (!empty($movieId)) {
        $query .= " AND m.id = ?";
        $params[] = $movieId;
        $param_types .= "i";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $movies = [];

    while ($row = $result->fetch_assoc()) {
        $row['available_seats'] = $row['total_seats'] - $row['occupied_seats'];
        $movies[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($movies);

    $stmt->close();
    $conn->close();
}
?>
