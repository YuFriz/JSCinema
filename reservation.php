<?php
require 'session_manager.php';
require 'db_connection.php';





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

// Start/reset timer if new screening is selected
if (!isset($_SESSION['reservation_timer_start']) || !isset($_SESSION['timer_screening_id']) || $_SESSION['timer_screening_id'] != $screening_id) {
    $_SESSION['reservation_timer_start'] = time();
    $_SESSION['timer_screening_id'] = $screening_id;
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 ms-2" href="profile.php" title="Profile" style="width: 42px; height: 42px;">
                            <i class="bi bi-person-circle fs-4"></i>
                        </a>
                    <?php else: ?>
                        <a class="nav-link d-flex align-items-center justify-content-center border rounded p-2 ms-2" href="register_login.php" title="Login/Register" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-arrow-in-right fs-4"></i>
                        </a>
                    <?php endif; ?>
                </li>

            </ul>
        </div>
    </div>
</nav>



<div class="alert alert-warning text-center fw-semibold" id="timerBox">
    Time remaining to complete reservation: <span id="timer">15:00</span>
</div>

<div class="stepper d-flex justify-content-between align-items-center my-4 px-md-5">
    <div class="step active">
        <div class="circle"></div>
        <div class="label">Tickets</div>
    </div>
    <div class="line active mx-2"></div>
    <div class="step active">
        <div class="circle"></div>
        <div class="label">Seats</div>
    </div>
    <div class="line mx-2"></div>
    <div class="step">
        <div class="circle"></div>
        <div class="label">Payment</div>
    </div>
</div>

<div class="container mt-4">
    <h3>Select Your Seats</h3>
    <form action="reservation_process.php" method="post">
        <form action="reservation_process.php" method="post" id="seatForm">
            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
            <input type="hidden" name="screening_id" value="<?php echo $screening_id; ?>">
            <input type="hidden" name="seat_rows" id="seat_rows">
            <input type="hidden" name="seat_numbers" id="seat_numbers">


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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        document.getElementById('seatForm').addEventListener('submit', function () {
            updateHiddenFields();
        });

    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timerElement = document.getElementById('timer');
        const timerBox = document.getElementById('timerBox');

        // Czas rozpoczęcia z PHP
        const startTime = <?php echo $_SESSION['reservation_timer_start']; ?>;
        const duration = 15 * 60 * 1000; // 15 minut w milisekundach

        function updateTimer() {
            const now = Date.now();
            const elapsed = now - (startTime * 1000);
            const remaining = duration - elapsed;

            if (remaining <= 0) {
                timerElement.innerText = "00:00";
                alert("Your reservation has expired. Please start over.");
                window.location.href = "index.php"; // lub dowolna strona startowa
            } else {
                const minutes = Math.floor(remaining / 60000);
                const seconds = Math.floor((remaining % 60000) / 1000);
                timerElement.innerText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }
        }

        updateTimer();
        setInterval(updateTimer, 1000); // aktualizacja co sekundę
    });
</script>



</body>
</html>
