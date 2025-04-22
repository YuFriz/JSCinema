<?php
require 'db_connection.php';
require_once('vendor/setasign/fpdf/fpdf.php');

if (empty($_GET['ids'])) {
    echo "<h2 style='color: red; text-align: center; margin-top: 50px;'>Ticket data not found. Please return to your order summary.</h2>";
    exit();
}

$ticket_ids = explode('_', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ticket_ids), '?'));
$types = str_repeat('i', count($ticket_ids));

$stmt = $conn->prepare("
    SELECT pt.id, pt.ticket_type, pt.price, pt.seat_id, s.row_number, s.seat_number,
           m.name AS movie_name, scr.screening_date, scr.start_time, a.name AS auditorium_name
    FROM purchased_tickets pt
    JOIN seats s ON pt.seat_id = s.id
    JOIN screenings scr ON pt.screening_id = scr.id
    JOIN auditoriums a ON scr.auditorium_id = a.id
    JOIN movies m ON pt.movie_id = m.id
    WHERE pt.id IN ($placeholders)
");
$stmt->bind_param($types, ...$ticket_ids);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Ticket(s) not found.");
}

$tickets = $result->fetch_all(MYSQLI_ASSOC);

// ✅ PDF start
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, "Cinema Ticket - JSCinema", 0, 1, 'C');
$pdf->Ln(5);

$screening = $tickets[0]; // Zakładamy, że wszystkie bilety dotyczą tego samego seansu

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(190, 10, "Movie: " . utf8_decode($screening['movie_name']), 0, 1);
$pdf->Cell(190, 10, "Date: " . $screening['screening_date'], 0, 1);
$pdf->Cell(190, 10, "Time: " . $screening['start_time'], 0, 1);
$pdf->Cell(190, 10, "Auditorium: " . utf8_decode($screening['auditorium_name']), 0, 1);
$pdf->Ln(5);

// Tabela z biletami
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, "Seat", 1);
$pdf->Cell(50, 10, "Ticket Type", 1);
$pdf->Cell(50, 10, "Price", 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($tickets as $ticket) {
    $pdf->Cell(50, 10, "Row {$ticket['row_number']}, Seat {$ticket['seat_number']}", 1);
    $pdf->Cell(50, 10, ucfirst($ticket['ticket_type']), 1);
    $pdf->Cell(50, 10, number_format($ticket['price'], 2) . " $", 1);
    $pdf->Ln();
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(190, 10, "Thank you for purchasing a ticket at JSCinema!", 0, 1, 'C');

// Output
ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ticket_' . implode('_', $ticket_ids) . '.pdf"');
$pdf->Output('I');
exit();
