<?php
require 'session_manager.php';
require_once 'vendor/autoload.php'; // FPDF

// Database connection
$conn = new mysqli('localhost', 'root', '', 'cinemajs');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$current_time = date('Y-m-d H:i:s');

// Fetch user tickets
$sql = "SELECT pt.id, pt.seat_id, pt.ticket_type, pt.price, s.screening_date, s.start_time, 
               m.name as movie_name
        FROM purchased_tickets pt
        JOIN screenings s ON pt.screening_id = s.id
        JOIN movies m ON s.movie_id = m.id
        WHERE pt.user_id = ?
        ORDER BY s.screening_date DESC, s.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$active_tickets = [];
$non_active_tickets = [];

while ($row = $result->fetch_assoc()) {
    $screening_datetime = $row['screening_date'] . ' ' . $row['start_time'];

    if ($screening_datetime > $current_time) {
        $active_tickets[] = $row; // Screening hasn't started yet
    } else {
        $non_active_tickets[] = $row; // Screening has already ended
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Profile Panel</a>
    </div>
</nav>

<div class="container my-5">
    <h2 class="mb-4">My Tickets</h2>

    <div class="btn-group mb-4">
        <button class="btn btn-success" id="showActive">Active Tickets</button>
        <button class="btn btn-secondary" id="showNonActive">Non-Active Tickets</button>
    </div>

    <div id="activeTickets">
        <h3>Active Tickets</h3>
        <?php if (empty($active_tickets)): ?>
            <p class="text-muted">You have no active tickets.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($active_tickets as $ticket): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Movie: <?php echo htmlspecialchars($ticket['movie_name']); ?></h5>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($ticket['screening_date']); ?></p>
                                <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($ticket['start_time']); ?></p>
                                <p class="card-text"><strong>Seat:</strong> <?php echo htmlspecialchars($ticket['seat_id']); ?></p>
                                <p class="card-text"><strong>Ticket Type:</strong> <?php echo htmlspecialchars($ticket['ticket_type']); ?></p>
                                <p class="card-text"><strong>Price:</strong> <?php echo number_format($ticket['price'], 2, ',', ' '); ?> $</p>
                                <a href="generate_ticket_p.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-outline-primary">Download PDF</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="nonActiveTickets" style="display: none;">
        <h3>Non-Active Tickets</h3>
        <?php if (empty($non_active_tickets)): ?>
            <p class="text-muted">You have no non-active tickets.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($non_active_tickets as $ticket): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border-secondary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Movie: <?php echo htmlspecialchars($ticket['movie_name']); ?></h5>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($ticket['screening_date']); ?></p>
                                <p class="card-text"><strong>Time:</strong> <?php echo htmlspecialchars($ticket['start_time']); ?></p>
                                <p class="card-text"><strong>Seat:</strong> <?php echo htmlspecialchars($ticket['seat_id']); ?></p>
                                <p class="card-text"><strong>Ticket Type:</strong> <?php echo htmlspecialchars($ticket['ticket_type']); ?></p>
                                <p class="card-text"><strong>Price:</strong> <?php echo number_format($ticket['price'], 2, ',', ' '); ?> $</p>
                                <button class="btn btn-outline-secondary disabled">Screening Ended</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('showActive').addEventListener('click', function () {
        document.getElementById('activeTickets').style.display = 'block';
        document.getElementById('nonActiveTickets').style.display = 'none';
    });

    document.getElementById('showNonActive').addEventListener('click', function () {
        document.getElementById('activeTickets').style.display = 'none';
        document.getElementById('nonActiveTickets').style.display = 'block';
    });
</script>

</body>
</html>
