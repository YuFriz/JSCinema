<?php
require 'db_connection.php';
require 'session_manager.php';

// Check admin permissions
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can analyze movies.");
}
$allowed_views = ['week', 'month', 'all'];
$view = (isset($_GET['view']) && in_array($_GET['view'], $allowed_views)) ? $_GET['view'] : 'all';


if ($view === 'week') {
    // Last 7 days
    $screening_count = $conn->query("SELECT COUNT(*) AS total FROM screenings WHERE screening_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['total'];
    $tickets_sold = $conn->query("SELECT COUNT(*) AS total FROM purchased_tickets WHERE purchase_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetch_assoc()['total'];
} elseif ($view === 'month') {
    // Current month
    $screening_count = $conn->query("SELECT COUNT(*) AS total FROM screenings WHERE MONTH(screening_date) = MONTH(CURDATE()) AND YEAR(screening_date) = YEAR(CURDATE())")->fetch_assoc()['total'];
    $tickets_sold = $conn->query("SELECT COUNT(*) AS total FROM purchased_tickets WHERE MONTH(purchase_date) = MONTH(CURDATE()) AND YEAR(purchase_date) = YEAR(CURDATE())")->fetch_assoc()['total'];
} else {
    // All-time
    $screening_count = $conn->query("SELECT COUNT(*) AS total FROM screenings")->fetch_assoc()['total'];
    $tickets_sold = $conn->query("SELECT COUNT(*) AS total FROM purchased_tickets")->fetch_assoc()['total'];
}


// Statistics
$movie_count = $conn->query("SELECT COUNT(*) AS total FROM movies")->fetch_assoc()['total'];

// Top 5 most watched movies
$popular_movies = $conn->query("SELECT m.name, COUNT(p.id) AS tickets FROM movies m 
    JOIN purchased_tickets p ON m.id = p.movie_id 
    GROUP BY m.id ORDER BY tickets DESC LIMIT 5");

// Weekly summary (past 7 days)
$weekly_summary = $conn->query("SELECT DATE(screening_date) AS date, COUNT(id) AS screenings, 
    (SELECT COUNT(*) FROM purchased_tickets WHERE screening_id IN 
        (SELECT id FROM screenings WHERE screening_date = s.screening_date)
    ) AS tickets_sold
    FROM screenings s
    WHERE screening_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(screening_date) ORDER BY DATE(screening_date) ASC");

// Monthly summary (past month grouped by year-month)
$monthly_summary = $conn->query("SELECT DATE_FORMAT(screening_date, '%Y-%m') AS month, COUNT(id) AS screenings, 
    (SELECT COUNT(*) FROM purchased_tickets WHERE screening_id IN 
        (SELECT id FROM screenings WHERE DATE_FORMAT(screening_date, '%Y-%m') = DATE_FORMAT(s.screening_date, '%Y-%m'))
    ) AS tickets_sold
    FROM screenings s
    WHERE screening_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
    GROUP BY DATE_FORMAT(screening_date, '%Y-%m') ORDER BY DATE_FORMAT(screening_date, '%Y-%m') ASC");

// Movies with status 'coming soon'
$coming_soon = $conn->query("SELECT name, movie_duration, created_at FROM movies WHERE status = 'soon in cinema' ORDER BY created_at DESC");


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
    <div class="text-center mb-3">
        <a href="?view=all" class="btn btn-outline-dark <?php echo $view === 'all' ? 'active' : ''; ?>">All</a>
        <a href="?view=month" class="btn btn-outline-primary <?php echo $view === 'month' ? 'active' : ''; ?>">Monthly</a>
        <a href="?view=week" class="btn btn-outline-secondary <?php echo $view === 'week' ? 'active' : ''; ?>">Weekly</a>
    </div>


    <div class="text-center mb-4">
        <a href="edit_banners.php" class="btn btn-warning">Edit Banner</a>
    </div>

    <!-- Summary Cards -->
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
                <p>Total Screenings (<?php echo ucfirst($view) === 'All' ? 'All Time' : ucfirst($view); ?>)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger text-center p-3">
                <h3><?php echo $tickets_sold; ?></h3>
                <p>Tickets Sold (<?php echo ucfirst($view); ?>)</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h4>Coming Soon Movies</h4>
            <?php if ($coming_soon->num_rows > 0): ?>
                <ul class="list-group">
                    <?php while ($movie = $coming_soon->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($movie['name']); ?>
                            <span class="badge bg-secondary rounded-pill">Coming Soon</span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted">There are currently no upcoming movies.</p>
            <?php endif; ?>
        </div>
    </div>



    <!-- Most Watched Movies -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h4>Top 5 Most Watched Movies</h4>
            <ul class="list-group">
                <?php while ($movie = $popular_movies->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo $movie['name']; ?>
                        <span class="badge bg-primary rounded-pill"><?php echo $movie['tickets']; ?> Tickets</span>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>

    <!-- Weekly and Monthly Summaries -->
    <div class="row mt-4">
        <?php if ($view === 'week' || $view === 'all'): ?>
            <div class="col-md-6">
                <h4>Summary of the Last 7 Days</h4>
                <ul class="list-group">
                    <?php while ($week = $weekly_summary->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <strong><?php echo $week['date']; ?></strong><br>
                            Screenings: <?php echo $week['screenings']; ?> <br>
                            Tickets Sold: <?php echo $week['tickets_sold']; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($view === 'month' || $view === 'all'): ?>
            <div class="col-md-6">
                <h4>Monthly Overview (Past Month)</h4>
                <ul class="list-group">
                    <?php while ($month = $monthly_summary->fetch_assoc()): ?>
                        <li class="list-group-item">
                            <strong><?php echo $month['month']; ?></strong><br>
                            Screenings: <?php echo $month['screenings']; ?> <br>
                            Tickets Sold: <?php echo $month['tickets_sold']; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
