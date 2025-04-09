<?php
global $conn;
require 'session_manager.php';
require 'db_connection.php';

// Pobranie statusu użytkownika
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['Status'] !== 'admin') {
        die("Access denied! Only admins can access this page.");
    }
} else {
    die("User not found!");
}

$stmt->close();

// Pobranie sal kinowych
$auditoriums = $conn->query("SELECT id, name FROM auditoriums");

// Pobranie repertuaru
$screenings = $conn->query("
    SELECT s.id, m.name AS movie_name, s.screening_date, s.start_time, a.name AS auditorium
    FROM screenings s
    JOIN movies m ON s.movie_id = m.id
    JOIN auditoriums a ON s.auditorium_id = a.id
    WHERE s.screening_date >= CURDATE()
    ORDER BY s.screening_date, s.start_time
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Auditoriums & Repertoire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Nawigacja -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<!-- MAIN -->
<div class="container mt-4">
    <h2 class="text-center mb-4">Admin Panel</h2>

    <!-- AUDITORIUM -->
    <div class="card-auditorium-admin shadow p-4 mb-5">
        <h3 class="text-center mb-3">Auditoriums</h3>
        <div class="row">
            <?php while ($row = $auditoriums->fetch_assoc()): ?>
                <div class="col-md-3 mb-3">
                    <a href="auditorium.php?id=<?= $row['id']; ?>" class="card auditorium-card text-decoration-none">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- REPERTOIRE -->
    <div class="card-repertoires-admin shadow p-4 rounded-4">
        <h3 class="text-center mb-4">
            🎬 <span class="fw-bold">Repertoire</span>
        </h3>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                <tr>
                    <th>Movie</th>
                    <th>Date 🗓️</th>
                    <th>Time ⏰</th>
                    <th>Auditorium 🎭</th>
                    <th>Actions ⚙️</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $screenings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['movie_name']); ?></td>
                        <td><span class="badge bg-light text-dark"><?= $row['screening_date']; ?></span></td>
                        <td><span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill"><?= substr($row['start_time'], 0, 5); ?></span></td>
                        <td><span class="text-dark fw-semibold"><?= htmlspecialchars($row['auditorium']); ?></span></td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="edit_screening.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">
                                    ✏️ Edit
                                </a>
                                <a href="delete_screening.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this screening?');">
                                    ❌ Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="add_screening.php" class="btn btn-success btn-lg px-4 shadow-sm">
                 Add New Screening
            </a>
        </div>
    </div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
