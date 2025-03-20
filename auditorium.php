<?php
global $conn;
require 'session_manager.php';
require 'db_connection.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can look on auditorium.");
}
$stmt->close();



if (!isset($_GET['id'])) {
    die("Invalid auditorium ID!");
}

$auditorium_id = intval($_GET['id']);
$auditorium_query = $conn->prepare("SELECT name FROM auditoriums WHERE id = ?");
$auditorium_query->bind_param("i", $auditorium_id);
$auditorium_query->execute();
$auditorium_result = $auditorium_query->get_result();

if ($auditorium_result->num_rows === 0) {
    die("Auditorium not found!");
}

$auditorium = $auditorium_result->fetch_assoc();

// Pobranie repertuaru dla danego audytorium
$screenings_query = $conn->prepare("
    SELECT s.id, m.name AS movie_name, s.screening_date, s.start_time 
    FROM screenings s
    JOIN movies m ON s.movie_id = m.id
    WHERE s.auditorium_id = ? AND s.screening_date >= CURDATE()
    ORDER BY s.screening_date, s.start_time
");
$screenings_query->bind_param("i", $auditorium_id);
$screenings_query->execute();
$screenings = $screenings_query->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repertoire - <?php echo htmlspecialchars($auditorium['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Nawigacja -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="auditorium_repertuar.php" class="btn btn-outline-light">Back</a>
    </div>
</nav>

<!-- Główna zawartość -->
<div class="container mt-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Repertoire for <?php echo htmlspecialchars($auditorium['name']); ?></h2>
    </div>

    <div class="row">
        <?php while ($row = $screenings->fetch_assoc()): ?>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-lg">
                    <div class="card-body text-center">
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($row['movie_name']); ?></h5>
                        <p class="card-text text-muted">
                            <strong>Date:</strong> <?php echo $row['screening_date']; ?><br>
                            <strong>Time:</strong> <?php echo $row['start_time']; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
