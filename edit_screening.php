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
    die("Access denied! Only admins can edit screenings.");
}
$stmt->close();

// Pobranie danych seansu
$screening_id = $_GET['id'];
$screening = $conn->query("SELECT * FROM screenings WHERE id = $screening_id")->fetch_assoc();
$movies = $conn->query("SELECT id, name FROM movies ORDER BY name ASC");
$auditoriums = $conn->query("SELECT id, name FROM auditoriums ORDER BY name ASC");

// Aktualizacja seansu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movie_id = $_POST['movie_id'];
    $auditorium_id = $_POST['auditorium_id'];
    $screening_date = $_POST['screening_date'];
    $start_time = $_POST['start_time'];

    $stmt = $conn->prepare("UPDATE screenings SET movie_id=?, auditorium_id=?, screening_date=?, start_time=? WHERE id=?");
    $stmt->bind_param("iissi", $movie_id, $auditorium_id, $screening_date, $start_time, $screening_id);

    if ($stmt->execute()) {
        header("Location: auditorium_repertuar.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Screening - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Główna sekcja -->
<div class="container mt-4">
    <h2 class="mb-4 text-center">Edit Screening</h2>

    <div class="card shadow p-4">
        <form method="post">
            <div class="mb-3">
                <label for="movie_id" class="form-label">Movie</label>
                <select class="form-select" id="movie_id" name="movie_id" required>
                    <?php while ($row = $movies->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $screening['movie_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="auditorium_id" class="form-label">Auditorium</label>
                <select class="form-select" id="auditorium_id" name="auditorium_id" required>
                    <?php while ($row = $auditoriums->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $screening['auditorium_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="screening_date" class="form-label">Screening Date</label>
                <input type="date" class="form-control" id="screening_date" name="screening_date" value="<?= $screening['screening_date'] ?>" required>
            </div>

            <div class="mb-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" class="form-control" id="start_time" name="start_time" value="<?= $screening['start_time'] ?>" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="auditorium_repertuar.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Update Screening</button>
            </div>
        </form>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
