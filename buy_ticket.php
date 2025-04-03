<?php
session_start();
require 'db_connection.php';

global $conn;

if (!isset($_GET['movie_id'], $_GET['screening_id'], $_GET['movie_name'], $_GET['start_time'])) {
    die("Error: Missing required data.");
}

$movie_id = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
$screening_id = filter_input(INPUT_GET, 'screening_id', FILTER_VALIDATE_INT);
$movie_name = htmlspecialchars($_GET['movie_name']);
$start_time = htmlspecialchars($_GET['start_time']);

if (!$movie_id || !$screening_id) {
    die("Error: Invalid movie or screening ID.");
}

// Fetch movie details
$query = $conn->prepare("SELECT movies.name, screenings.start_time, screenings.screening_date, screenings.id AS screening_id, movies.description FROM movies JOIN screenings ON movies.id = screenings.movie_id WHERE movies.id = ? AND screenings.id = ? LIMIT 1");
$query->bind_param("ii", $movie_id, $screening_id);
$query->execute();
$result = $query->get_result();
$movie = $result->fetch_assoc();

if (!$movie || strtotime($movie['screening_date']) < strtotime(date('Y-m-d'))) {
    header("Location: index.php");
    exit();
}

// Get auditorium_id
$audQuery = $conn->prepare("SELECT auditorium_id FROM screenings WHERE id = ?");
$audQuery->bind_param("i", $screening_id);
$audQuery->execute();
$audRes = $audQuery->get_result();
$audRow = $audRes->fetch_assoc();
$auditorium_id = $audRow['auditorium_id'] ?? 0;

// Count available seats
$seatsQuery = $conn->prepare("SELECT COUNT(*) AS available FROM seats WHERE auditorium_id = ? AND id NOT IN (SELECT seat_id FROM purchased_tickets WHERE screening_id = ?)");
$seatsQuery->bind_param("ii", $auditorium_id, $screening_id);
$seatsQuery->execute();
$seatsRes = $seatsQuery->get_result();
$available_seats = $seatsRes->fetch_assoc()['available'] ?? 0;

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

    <div id="alertBox" class="alert alert-warning d-none text-center" role="alert">
        Please select at least one ticket before proceeding.
    </div>
<div class="container mt-4">
    <?php
    $timeFormatted = date('H:i', strtotime($movie['start_time']));
    $screeningDate = date('Y-m-d', strtotime($movie['screening_date']));

    $dayName = date('l', strtotime($movie['screening_date'])); // np. Thursday
    $dateFormatted = date('j.m.Y', strtotime($movie['screening_date']));
    ?>

    <div class="d-flex justify-content-between align-items-center flex-wrap my-4">
        <div class="text-start">
            <h1 class="mb-2"><?php echo htmlspecialchars($movie['name']); ?></h1>
            <p class="mb-1"><strong>Date:</strong> <?php echo $dayName . ', ' . $dateFormatted; ?></p>
            <p><strong>Start time:</strong> <?php echo $timeFormatted; ?></p>
            <p><strong>Auditorium:</strong> <?php echo $auditorium_id; ?></p>
        </div>

        <div class="text-end mt-3 mt-md-0">
            <p><b>Seats available:</b></p>
            <div class="progress mt-2" style="width: 200px; height: 20px;">
                <div id="seatsProgress" class="progress-bar bg-warning text-dark fw-bold" role="progressbar" style="width: 100%;">
                </div>
            </div>
        </div>

    </div>

    <div class="stepper d-flex justify-content-between align-items-center my-4 px-md-5">
        <div class="step active">
            <div class="circle"></div>
            <div class="label">Tickets</div>
        </div>
        <div class="line flex-grow-1 mx-2"></div>
        <div class="step">
            <div class="circle"></div>
            <div class="label">Seats</div>
        </div>
        <div class="line flex-grow-1 mx-2"></div>
        <div class="step">
            <div class="circle"></div>
            <div class="label">Payment</div>
        </div>
    </div>


    <form id="ticketForm" action="reservation.php" method="post">
    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
        <input type="hidden" name="screening_id" value="<?php echo $screening_id; ?>">
        <input type="hidden" name="movie_name" value="<?php echo htmlspecialchars($movie['name']); ?>">
        <input type="hidden" name="start_time" value="<?php echo htmlspecialchars($movie['start_time']); ?>">
        <input type="hidden" id="available_seats" value="<?php echo $available_seats; ?>">
        <input type="hidden" name="total_tickets" value="0">

        <div class="table-wrapper text-center">
            <table class="table ticket-table mx-auto w-auto">
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
                    <div class="input-group ticket-quantity" data-type="regular">
                        <button type="button" class="btn btn-outline-secondary decrement">−</button>
                        <input type="number" class="form-control text-center quantity-input" name="regular" value="0" min="0" max="<?php echo $available_seats; ?>">
                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                    </div>
                </td>
            </tr>

            <tr>
                <td>Children's Ticket</td>
                <td>4.50 €</td>
                <td>
                    <div class="input-group ticket-quantity" data-type="regular">
                        <button type="button" class="btn btn-outline-secondary decrement">−</button>
                        <input type="number" class="form-control text-center quantity-input" name="children" value="0" min="0" max="<?php echo $available_seats; ?>">
                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                    </div>
                </td>
            </tr>




            <?php if (isset($_SESSION['user_id'])) : ?>
                <tr><td>CLUB Ticket</td><td>5.00 €</td>
                    <td>
                        <div class="input-group ticket-quantity" data-type="regular">
                            <button type="button" class="btn btn-outline-secondary decrement">−</button>
                            <input type="number" class="form-control text-center quantity-input" name="club" value="0" min="0" max="<?php echo $available_seats; ?>">
                            <button type="button" class="btn btn-outline-secondary increment">+</button>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <tr><td>Youth Ticket</td><td>5.50 €</td>
                <td>
                    <div class="input-group ticket-quantity" data-type="regular">
                        <button type="button" class="btn btn-outline-secondary decrement">−</button>
                        <input type="number" class="form-control text-center quantity-input" name="youth" value="0" min="0" max="<?php echo $available_seats; ?>">
                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                    </div>
                </td>
            </tr>
            <tr><td>Senior/Disabled Ticket</td><td>4.00 €</td>
                <td>
                    <div class="input-group ticket-quantity" data-type="regular">
                        <button type="button" class="btn btn-outline-secondary decrement">−</button>
                        <input type="number" class="form-control text-center quantity-input" name="senior" value="0" min="0" max="<?php echo $available_seats; ?>">
                        <button type="button" class="btn btn-outline-secondary increment">+</button>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
            <button type="submit" class="btn btn-primary">Select Seats</button>
        </div>
    </form>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <tr>
            <td colspan="3">
                <div class="alert bg-light border d-flex justify-content-between align-items-center mt-4 p-3 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-star-fill text-warning fs-4 me-3"></i>
                        <div>
                            <strong>JSCinema Club</strong> members get discount on tickets 😉
                            <br>
                            Membership is <strong>free</strong>. Log in or register now!
                        </div>
                    </div>
                    <a href="register_login.php" class="btn btn-outline-warning fw-semibold">Join Now</a>
                </div>
            </td>
        </tr>
    <?php endif; ?>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const maxSeats = parseInt(document.getElementById('available_seats').value);
        const inputs = document.querySelectorAll('.quantity-input');
        const totalTicketsInput = document.querySelector('input[name="total_tickets"]');
        const progressBar = document.getElementById('seatsProgress');

        function updateProgress() {
            let total = 0;
            inputs.forEach(input => total += parseInt(input.value));
            totalTicketsInput.value = total;

            const remaining = maxSeats - total;
            const percent = (remaining / maxSeats) * 100;

            if (progressBar) {
                progressBar.style.width = percent + '%';
                progressBar.innerText = `${remaining}`;
            }
        }


        inputs.forEach(input => {
            input.addEventListener('input', () => {
                let val = parseInt(input.value) || 0;
                if (val < 0) val = 0;
                if (val > maxSeats) val = maxSeats;
                input.value = val;
                updateProgress();
            });
        });


        document.querySelectorAll('.ticket-quantity').forEach(group => {
            const input = group.querySelector('.quantity-input');
            const increment = group.querySelector('.increment');
            const decrement = group.querySelector('.decrement');

            increment.addEventListener('click', () => {
                const currentTotal = Array.from(inputs).reduce((sum, i) => sum + parseInt(i.value), 0);
                if (currentTotal < maxSeats && parseInt(input.value) < parseInt(input.max)) {
                    input.value = parseInt(input.value) + 1;
                    updateProgress();
                }
            });

            decrement.addEventListener('click', () => {
                if (parseInt(input.value) > 0) {
                    input.value = parseInt(input.value) - 1;
                    updateProgress();
                }
            });
        });

        updateProgress();
        document.getElementById('ticketForm').addEventListener('submit', function (e) {
            const total = parseInt(document.querySelector('input[name="total_tickets"]').value);
            const alertBox = document.getElementById('alertBox');

            if (total === 0) {
                e.preventDefault(); // zatrzymaj wysyłkę formularza
                alertBox.classList.remove('d-none'); // pokaż alert
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alertBox.classList.add('d-none'); // ukryj, jeśli wszystko ok
            }
        });


    });
</script>


</body>
</html>
