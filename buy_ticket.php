<?php
session_start();
require 'db_connection.php';

global $conn;

if (!isset($_GET['movie_id']) || !isset($_GET['screening_id']) || !isset($_GET['movie_name']) || !isset($_GET['start_time'])) {
    die("Error: Missing required data.");
}

$movie_id = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT) ?? null;
$screening_id = filter_input(INPUT_GET, 'screening_id', FILTER_VALIDATE_INT) ?? null;
$movie_name = htmlspecialchars($_GET['movie_name'] ?? '');
$start_time = htmlspecialchars($_GET['start_time'] ?? '');

if (!$movie_id || !$screening_id) {
    die("Error: Invalid movie or screening ID.");
}

$query = $conn->prepare("SELECT movies.name, screenings.start_time, screenings.screening_date, screenings.id AS screening_id, movies.description FROM movies JOIN screenings ON movies.id = screenings.movie_id WHERE movies.id = ? AND screenings.id = ? LIMIT 1");
$query->bind_param("ii", $movie_id, $screening_id);
$query->execute();
$result = $query->get_result();
$movie = $result->fetch_assoc();

if (!$movie || strtotime($movie['screening_date']) < strtotime(date('Y-m-d'))) {
    header("Location: index.php");
    exit();
}

$_SESSION['selected_tickets'] = [
    'movie_id' => $movie_id,
    'screening_id' => $screening_id,
    'movie_name' => $movie_name,
    'start_time' => $start_time,
    'total_tickets' => $_POST['total_tickets'] ?? 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Reservation - <?php echo htmlspecialchars($movie['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="bg-dark text-white p-3">
    <div class="container">
        <h1><a href="index.php" class="text-white text-decoration-none">JSCinema</a></h1>
    </div>
</header>

<div class="container mt-4">
    <h1><?php echo htmlspecialchars($movie['name']); ?></h1>
    <p><?php echo htmlspecialchars($movie['description']); ?></p>
    <p>Start time: <?php echo htmlspecialchars($movie['start_time']); ?></p>

    <h2>Select Tickets</h2>

    <form action="reservation.php" method="post">
        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" name="screening_id" value="<?php echo $screening_id; ?>">
        <input type="hidden" name="movie_name" value="<?php echo htmlspecialchars($movie['name']); ?>">
        <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($movie['start_time']); ?>">
        <input type="hidden" name="total_tickets" value="0">

        <table class="table">
            <thead>
            <tr>
                <th>Ticket Type</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Regular Ticket</td>
                <td>7.99 €</td>
                <td>
                    <select name="regular" class="form-select">
                        <?php for ($i = 0; $i <= 5; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Children's Ticket</td>
                <td>4.50 €</td>
                <td>
                    <select name="children" class="form-select">
                        <?php for ($i = 0; $i <= 5; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </td>
            </tr>
            <?php if (isset($_SESSION['user_id'])) : ?>
                <tr>
                    <td>CLUB Ticket</td>
                    <td>5.00 €</td>
                    <td>
                        <select name="club" class="form-select">
                            <?php for ($i = 0; $i <= 5; $i++) echo "<option value='$i'>$i</option>"; ?>
                        </select>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td>Youth Ticket</td>
                <td>5.50 €</td>
                <td>
                    <select name="youth" class="form-select">
                        <?php for ($i = 0; $i <= 5; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Senior/Disabled Ticket</td>
                <td>4.00 €</td>
                <td>
                    <select name="senior" class="form-select">
                        <?php for ($i = 0; $i <= 5; $i++) echo "<option value='$i'>$i</option>"; ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Select Seats</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let selects = document.querySelectorAll('select');
        let totalTicketsInput = document.querySelector('input[name="total_tickets"]');

        function updateTotalTickets() {
            let total = 0;
            selects.forEach(select => {
                total += parseInt(select.value);
            });
            totalTicketsInput.value = total;
        }

        selects.forEach(select => {
            select.addEventListener('change', updateTotalTickets);
        });
    });
</script>

</body>
</html>
