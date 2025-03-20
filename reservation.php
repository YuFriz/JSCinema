<?php
require 'session_manager.php';
require 'db_connection.php';

global $conn;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['movie_id']) && isset($_POST['screening_id'])) {
    $movie_id = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $screening_id = filter_input(INPUT_POST, 'screening_id', FILTER_VALIDATE_INT);

    if (!$movie_id || !$screening_id) {
        die("Error: Invalid movie or screening ID.");
    }

    $ticket_types = [
        'regular' => 7.99,
        'children' => 4.5,
        'club' => 5.0,
        'youth' => 5.5,
        'senior' => 4.0
    ];

    $selected_tickets = [];
    $total_tickets = 0;

    foreach ($ticket_types as $type => $price) {
        $count = filter_input(INPUT_POST, $type, FILTER_VALIDATE_INT) ?? 0;
        if ($count > 0) {
            $selected_tickets[$type] = [
                'count' => $count,
                'price' => $price
            ];
            $total_tickets += $count;
        }
    }

    if ($total_tickets == 0) {
        die("Error: You must select at least one ticket.");
    }

    $_SESSION['selected_tickets'] = [
        'movie_id' => $movie_id,
        'screening_id' => $screening_id,
        'total_tickets' => $total_tickets,
        'tickets' => $selected_tickets
    ];
}

if (!isset($_SESSION['selected_tickets'])) {
    die("No screening selected.");
}

$movie_id = $_SESSION['selected_tickets']['movie_id'];
$screening_id = $_SESSION['selected_tickets']['screening_id'];
$total_tickets = $_SESSION['selected_tickets']['total_tickets'];
$selected_tickets = $_SESSION['selected_tickets']['tickets'];

$query = $conn->prepare("SELECT auditoriums.id AS auditorium_id FROM screenings JOIN auditoriums ON screenings.auditorium_id = auditoriums.id WHERE screenings.id = ?");
$query->bind_param("i", $screening_id);
$query->execute();
$result = $query->get_result();
$auditorium = $result->fetch_assoc();

if (!$auditorium) {
    die("Error: Auditorium not found for this screening.");
}

$auditorium_id = $auditorium['auditorium_id'];

$seats_query = $conn->prepare("
    SELECT s.id, s.seat_number, s.row_number, CASE WHEN pt.seat_id IS NOT NULL THEN 1 ELSE 0 END AS is_taken 
    FROM seats s 
    LEFT JOIN purchased_tickets pt ON s.id = pt.seat_id AND pt.screening_id = ? 
    WHERE s.auditorium_id = ? 
    ORDER BY s.row_number, s.seat_number
");
$seats_query->bind_param("ii", $screening_id, $auditorium_id);
$seats_query->execute();
$seats_result = $seats_query->get_result();

$seats = [];
while ($row = $seats_result->fetch_assoc()) {
    $seats[$row['row_number']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Selection</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container mt-4">
    <h3>Select Your Seats</h3>
    <form action="reservation_process.php" method="post">
        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" name="screening_id" value="<?php echo $screening_id; ?>">

        <div class="seating-container">
            <?php foreach ($seats as $row_number => $row_seats): ?>
                <div class="seat-row-container">
                    <div class="row-label"><?php echo "Row " . htmlspecialchars($row_number); ?></div>
                    <div class="seat-row">
                        <?php
                        // Sortowanie miejsc w rzędzie po numerze siedzenia
                        usort($row_seats, function($a, $b) {
                            return $a['seat_number'] - $b['seat_number'];
                        });

                        foreach ($row_seats as $seat): ?>
                            <label class="seat btn btn-outline-secondary <?php echo $seat['is_taken'] ? 'taken' : ''; ?>">
                                <input type="checkbox" name="seats[]" value="<?php echo $seat['id']; ?>" class="d-none seat-checkbox"
                                       data-row="<?php echo htmlspecialchars($row_number); ?>"
                                       data-seat="<?php echo htmlspecialchars($seat['seat_number']); ?>"
                                    <?php echo $seat['is_taken'] ? 'disabled' : ''; ?>>
                                <?php echo htmlspecialchars($seat['seat_number']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <input type="hidden" name="seat_rows" id="seat_rows">
        <input type="hidden" name="seat_numbers" id="seat_numbers">




        <h3 class="mt-4">Ticket Summary:</h3>
        <ul class="list-group">
            <?php foreach ($selected_tickets as $type => $ticket): ?>
                <li class="list-group-item"> <?php echo ucfirst($type); ?>: <?php echo $ticket['count']; ?> x <?php echo $ticket['price']; ?> €</li>
                <input type="hidden" name="ticket_types[<?php echo $type; ?>]" value="<?php echo $ticket['count']; ?>">
            <?php endforeach; ?>
        </ul>

        <button type="submit" class="btn btn-primary mt-3">Confirm Selection</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let checkboxes = document.querySelectorAll('.seat-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                let seatLabel = this.parentElement;
                if (this.checked) {
                    seatLabel.classList.add('selected');
                } else {
                    seatLabel.classList.remove('selected');
                }

                // Aktualizacja ukrytych pól
                updateHiddenFields();
            });
        });

        function updateHiddenFields() {
            let selectedSeats = document.querySelectorAll('.seat-checkbox:checked');
            let seatRows = [];
            let seatNumbers = [];

            selectedSeats.forEach(seat => {
                seatRows.push(seat.getAttribute('data-row'));
                seatNumbers.push(seat.getAttribute('data-seat'));
            });

            document.getElementById('seat_rows').value = JSON.stringify(seatRows);
            document.getElementById('seat_numbers').value = JSON.stringify(seatNumbers);
        }
    });
</script>


</body>
</html>
