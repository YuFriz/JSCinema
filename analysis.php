<?php
require 'db_connection.php';
require 'session_manager.php';


// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can analyze movies.");
}
$stmt->close();




// Pobranie liczby filmów
$movie_count = $conn->query("SELECT COUNT(*) AS total FROM movies")->fetch_assoc()['total'];

// Pobranie liczby seansów
$screening_count = $conn->query("SELECT COUNT(*) AS total FROM screenings")->fetch_assoc()['total'];

// Pobranie liczby sprzedanych biletów
$tickets_sold = $conn->query("SELECT COUNT(*) AS total FROM purchased_tickets")->fetch_assoc()['total'];

// Pobranie najczęściej oglądanych filmów
$popular_movies = $conn->query("SELECT m.name, COUNT(p.id) AS tickets FROM movies m 
    JOIN purchased_tickets p ON m.id = p.movie_id 
    GROUP BY m.id ORDER BY tickets DESC LIMIT 5");

// Pobranie najpopularniejszych gatunków
$popular_genres = $conn->query("SELECT g.name, COUNT(mg.movie_id) AS count FROM genres g
    JOIN movie_genres mg ON g.id = mg.genre_id
    JOIN purchased_tickets p ON mg.movie_id = p.movie_id
    GROUP BY g.id ORDER BY count DESC LIMIT 5");

// Podsumowanie tygodniowe
$weekly_summary = $conn->query("SELECT DATE(screening_date) AS date, COUNT(id) AS screenings, 
    (SELECT COUNT(*) FROM purchased_tickets WHERE screening_id IN (SELECT id FROM screenings WHERE screening_date = s.screening_date)) AS tickets_sold
    FROM screenings s
    WHERE screening_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(screening_date) ORDER BY DATE(screening_date) ASC");

// Podsumowanie miesięczne
$monthly_summary = $conn->query("SELECT DATE_FORMAT(screening_date, '%Y-%m') AS month, COUNT(id) AS screenings, 
    (SELECT COUNT(*) FROM purchased_tickets WHERE screening_id IN (SELECT id FROM screenings WHERE DATE_FORMAT(screening_date, '%Y-%m') = DATE_FORMAT(s.screening_date, '%Y-%m'))) AS tickets_sold
    FROM screenings s
    WHERE screening_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY DATE_FORMAT(screening_date, '%Y-%m') ORDER BY DATE_FORMAT(screening_date, '%Y-%m') ASC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Analysis - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Admin Panel</a>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center">Admin Analysis</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-bg-primary text-center p-3">
                <h3><?php echo $movie_count; ?></h3>
                <p>Total Movies</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-success text-center p-3">
                <h3><?php echo $screening_count; ?></h3>
                <p>Total Screenings</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger text-center p-3">
                <h3><?php echo $tickets_sold; ?></h3>
                <p>Tickets Sold</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h4>Most Watched Movies</h4>
            <ul class="list-group">
                <?php while ($movie = $popular_movies->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo $movie['name']; ?>
                        <span class="badge bg-primary rounded-pill"><?php echo $movie['tickets']; ?> Tickets</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h4>Popular Genres</h4>
            <ul class="list-group">
                <?php while ($genre = $popular_genres->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo $genre['name']; ?>
                        <span class="badge bg-success rounded-pill"><?php echo $genre['count']; ?> Movies</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <h4>Weekly Summary</h4>
            <ul class="list-group">
                <?php while ($week = $weekly_summary->fetch_assoc()): ?>
                    <li class="list-group-item">Date: <?php echo $week['date']; ?> - Screenings: <?php echo $week['screenings']; ?>, Tickets Sold: <?php echo $week['tickets_sold']; ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h4>Monthly Summary</h4>
            <ul class="list-group">
                <?php while ($month = $monthly_summary->fetch_assoc()): ?>
                    <li class="list-group-item">Month: <?php echo $month['month']; ?> - Screenings: <?php echo $month['screenings']; ?>, Tickets Sold: <?php echo $month['tickets_sold']; ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
