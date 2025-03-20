<?php
require 'db_connection.php';

if (!isset($_GET['movie_id'], $_GET['screening_date'], $_GET['start_time'])) {
    echo json_encode(['error' => 'Brak wymaganych parametrów']);
    exit;
}

$movie_id = $_GET['movie_id'];
$screening_date = $_GET['screening_date'];
$start_time = $_GET['start_time'];

// Pobranie audytoriów zajętych w danym przedziale czasowym
$sql = "SELECT s.auditorium_id 
        FROM screenings s
        JOIN movies m ON s.movie_id = m.id
        WHERE s.screening_date = ? 
        AND (
            (s.start_time <= ? AND ADDTIME(s.start_time, SEC_TO_TIME(m.movie_duration * 60)) > ?)
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $screening_date, $start_time, $start_time);
$stmt->execute();
$result = $stmt->get_result();

$occupied_auditoriums = [];
while ($row = $result->fetch_assoc()) {
    $occupied_auditoriums[] = $row['auditorium_id'];
}
$stmt->close();

// Pobranie wszystkich sal kinowych
$sql = "SELECT id, name FROM auditoriums";
$result = $conn->query($sql);

$auditoriums = [];
while ($row = $result->fetch_assoc()) {
    $auditoriums[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'occupied' => in_array($row['id'], $occupied_auditoriums)
    ];
}

// Zwrot danych w formacie JSON
header('Content-Type: application/json');
echo json_encode(['auditoriums' => $auditoriums]);
?>
