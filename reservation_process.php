<?php
session_start();
require 'db_connection.php';
require 'vendor/autoload.php';

unset($_SESSION['reservation_timer_start'], $_SESSION['timer_screening_id']);


$step = $_GET['step'] ?? 'confirm';

if ($step === 'confirm') {
    if (empty($_POST['seats']) || empty($_POST['ticket_types']) || empty($_POST['movie_id']) || empty($_POST['screening_id']) || empty($_POST['seat_rows']) || empty($_POST['seat_numbers'])) {
        die("Error: Invalid reservation data.");
    }

    $_SESSION['reservation'] = [
        'movie_id' => $_POST['movie_id'],
        'screening_id' => $_POST['screening_id'],
        'seats' => $_POST['seats'],
        'seat_rows' => json_decode($_POST['seat_rows'], true), // Dekodowanie tablicy JSON
        'seat_numbers' => json_decode($_POST['seat_numbers'], true),
        'ticket_types' => $_POST['ticket_types']
    ];

    header("Location: reservation_process.php?step=finalize");
    exit();
}


if ($step === 'finalize') {
    if (empty($_SESSION['reservation'])) {
        die("Error: No reservation data found.");
    }

    $user_id = $_SESSION['user_id'];
    $movie_id = $_SESSION['reservation']['movie_id'];
    $screening_id = $_SESSION['reservation']['screening_id'];
    $selected_seats = $_SESSION['reservation']['seats'];
    $seat_rows = $_SESSION['reservation']['seat_rows'];
    $seat_numbers = $_SESSION['reservation']['seat_numbers'];
    $ticket_types = $_SESSION['reservation']['ticket_types'];

    $ticket_prices = [
        'regular' => 7.99,
        'children' => 4.5,
        'club' => 5.0,
        'youth' => 5.5,
        'senior' => 4.0
    ];

    $conn->begin_transaction();
    $ticket_ids = [];

    try {
        $stmt = $conn->prepare("
                INSERT INTO purchased_tickets (user_id, movie_id, screening_id, seat_id, ticket_type, price, purchase_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

        $seat_details_stmt = $conn->prepare("SELECT row_number, seat_number FROM seats WHERE id = ?");

        foreach ($ticket_types as $type => $count) {
            $price = $ticket_prices[$type] ?? 0;

            for ($i = 0; $i < $count; $i++) {
                if (empty($selected_seats)) {
                    throw new Exception("Error: No available seats.");
                }

                $seat_id = array_shift($selected_seats);

                // Pobierz row_number i seat_number dla danego seat_id
                $seat_details_stmt->bind_param("i", $seat_id);
                $seat_details_stmt->execute();
                $seat_details_result = $seat_details_stmt->get_result();
                $seat_details = $seat_details_result->fetch_assoc();
                if (!$seat_details) {
                    throw new Exception("Error: Seat details not found.");
                }

                $stmt->bind_param("iiiisd", $user_id, $movie_id, $screening_id, $seat_id, $type, $price);
                $stmt->execute();

                $last_id = $conn->insert_id;
                if ($last_id == 0) {
                    throw new Exception("Error: Could not retrieve ticket ID.");
                }

                $ticket_ids[] = $last_id;
            }
        }


        if (empty($ticket_ids)) {
            throw new Exception("Error: No tickets were saved.");
        }

        $conn->commit();
        $_SESSION['ticket_ids'] = $ticket_ids;
        $_SESSION['purchased_tickets'] = [
            'movie_id' => $movie_id,
            'screening_id' => $screening_id,
            'seats' => $_SESSION['reservation']['seats'],
            'seat_rows' => [],
            'seat_numbers' => [],
            'ticket_types' => $_SESSION['reservation']['ticket_types']
        ];

// Pobierz rzędy i numery siedzeń dla każdego seat_id
        foreach ($_SESSION['purchased_tickets']['seats'] as $seat_id) {
            $seat_details_stmt->bind_param("i", $seat_id);
            $seat_details_stmt->execute();
            $seat_details_result = $seat_details_stmt->get_result();
            $seat_details = $seat_details_result->fetch_assoc();

            if ($seat_details) {
                $_SESSION['purchased_tickets']['seat_rows'][] = $seat_details['row_number'];
                $_SESSION['purchased_tickets']['seat_numbers'][] = $seat_details['seat_number'];
            }
        }

        unset($_SESSION['reservation']);

        header("Location: reservation_process.php?step=success");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error during reservation: " . $e->getMessage());
    }
}


if ($step === 'success') {
    if (empty($_SESSION['ticket_ids'])) {
        die("Error: No tickets saved.");
    }

    $pdf_filename = "ticket_" . implode("_", $_SESSION['ticket_ids']) . ".pdf";
    $_SESSION['generated_ticket'] = $pdf_filename;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Thank You for Your Purchase</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css">
        <script>
            setTimeout(function() {
                document.getElementById('downloadLink').click();
            }, 10000);
        </script>
    </head>
    <body class="container text-center mt-5">

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
        <div class="line active mx-2"></div>
        <div class="step active">
            <div class="circle"></div>
            <div class="label">Payment</div>
        </div>
    </div>
    <h1>Thank You for Your Purchase at JSCinema</h1>
    <p>Your purchase has been successfully completed.</p>

    <a id="downloadLink" href="ticket_generate.php" class="btn btn-primary">Download Ticket</a>

    <p class="mt-3">If you do not download manually, your ticket will be downloaded automatically in 10 seconds.</p>
    </body>
    </html>
    <?php
}
?>