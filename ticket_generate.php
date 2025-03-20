<?php
global $conn;
session_start();
require 'db_connection.php';
require 'vendor/autoload.php';

// **CHECK IF HEADERS HAVE ALREADY BEEN SENT**
if (headers_sent($file, $line)) {
    die("Error: Headers already sent in file: $file, line: $line");
}

// Retrieve data from session
$ticket_ids = $_SESSION['ticket_ids'];
$movie_id = $_SESSION['purchased_tickets']['movie_id'];
$screening_id = $_SESSION['purchased_tickets']['screening_id'];
$seats = $_SESSION['purchased_tickets']['seats'];
$ticket_types = $_SESSION['purchased_tickets']['ticket_types'];

// **Create PDF**
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, "Cinema Ticket - JSCinema", 0, 1, 'C');
$pdf->Ln(5);

// **Movie and screening details**
$stmt = $conn->prepare("
    SELECT m.name AS movie_name, s.screening_date, s.start_time, a.name AS auditorium_name
    FROM screenings s
    JOIN movies m ON s.movie_id = m.id
    JOIN auditoriums a ON s.auditorium_id = a.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $screening_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Screening information not found.");
}
$screening = $result->fetch_assoc();
$stmt->close();

// **Add details to PDF**
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 10, "Movie: " . utf8_decode($screening['movie_name']), 0, 1);
$pdf->Cell(190, 10, "Date: " . $screening['screening_date'], 0, 1);
$pdf->Cell(190, 10, "Time: " . $screening['start_time'], 0, 1);
$pdf->Cell(190, 10, "Auditorium: " . utf8_decode($screening['auditorium_name']), 0, 1);
$pdf->Ln(5);

// **Add tickets**
$pdf->Cell(50, 10, "Seat", 1);
$pdf->Cell(50, 10, "Ticket Type", 1);
$pdf->Cell(50, 10, "Price", 1);
$pdf->Ln();

$ticket_prices = [
    'regular' => 7.99,
    'children' => 4.5,
    'club' => 5.0,
    'youth' => 5.5,
    'senior' => 4.0
];

$seat_index = 0; // Indeks dla numeracji miejsc
$assigned_tickets = []; // Tablica do śledzenia przypisanych typów biletów

foreach ($ticket_types as $type => $count) {
    $price = $ticket_prices[$type] ?? 0;

    for ($i = 0; $i < $count; $i++) {
        if (!isset($seats[$seat_index])) {
            continue; // Jeśli nie ma więcej siedzeń, pomijamy
        }

        $seat_id = $seats[$seat_index];

        // Pobieranie numeru rzędu i numeru siedzenia
        $seat_query = $conn->prepare("SELECT row_number, seat_number FROM seats WHERE id = ?");
        $seat_query->bind_param("i", $seat_id);
        $seat_query->execute();
        $seat_result = $seat_query->get_result();
        $seat_data = $seat_result->fetch_assoc();
        $seat_query->close();

        if (!$seat_data) {
            continue;
        }

        $row_number = $seat_data['row_number'];
        $seat_number = $seat_data['seat_number'];

        // Zapisujemy przypisane miejsce i typ biletu
        $assigned_tickets[] = [
            'row' => $row_number,
            'seat' => $seat_number,
            'type' => $type,
            'price' => $price
        ];

        $seat_index++; // Przechodzimy do kolejnego miejsca
    }
}

// **Teraz generujemy PDF na podstawie `assigned_tickets`**
foreach ($assigned_tickets as $ticket) {
    $pdf->Cell(50, 10, "Row {$ticket['row']}, Seat {$ticket['seat']}", 1);
    $pdf->Cell(50, 10, ucfirst($ticket['type']), 1);
    $pdf->Cell(50, 10, number_format($ticket['price'], 2) . " $", 1);
    $pdf->Ln();
}



$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(190, 10, "Thank you for purchasing a ticket at JSCinema!", 0, 1, 'C');

ob_end_clean(); // Instead of `ob_clean(); flush();`
// **Direct file download**
$pdf_filename = "ticket_" . implode("_", $ticket_ids) . ".pdf";
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
$pdf->Output('I');

// ✅ Remove `ticket_ids` after download
unset($_SESSION['ticket_ids']);

exit();
