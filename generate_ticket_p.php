<?php
require 'session_manager.php';
require_once('vendor/setasign/fpdf/fpdf.php');
require 'db_connection.php';

if (!isset($_GET['ticket_id'])) {
    die("No ticket ID provided.");
}

$ticket_id = intval($_GET['ticket_id']);

// Pobranie informacji o bilecie
$sql = "SELECT pt.id, pt.seat_id, pt.ticket_type, pt.price, pt.purchase_date, 
               s.screening_date, s.start_time, s.auditorium_id, 
               m.name as movie_name, a.seat_number, a.row_number
        FROM purchased_tickets pt
        JOIN screenings s ON pt.screening_id = s.id
        JOIN movies m ON s.movie_id = m.id
        JOIN seats a ON pt.seat_id = a.id
        WHERE pt.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Ticket not found.");
}

$ticket = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Tworzenie biletu w PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Cinema Ticket - JSCinema', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(50, 10, 'Movie: ' . $ticket['movie_name'], 0, 1);
$pdf->Cell(50, 10, 'Date: ' . $ticket['screening_date'], 0, 1);
$pdf->Cell(50, 10, 'Time: ' . $ticket['start_time'], 0, 1);
$pdf->Cell(50, 10, 'Auditorium: Auditorium ' . $ticket['auditorium_id'], 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 10, 'Seat', 1);
$pdf->Cell(60, 10, 'Ticket Type', 1);
$pdf->Cell(60, 10, 'Price', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(60, 10, 'Row ' . $ticket['row_number'] . ', Seat ' . $ticket['seat_number'], 1);
$pdf->Cell(60, 10, $ticket['ticket_type'], 1);
$pdf->Cell(60, 10, number_format($ticket['price'], 2) . ' $', 1);
$pdf->Ln(20);

$pdf->SetFont('Arial', 'I', 12);
$pdf->Cell(190, 10, 'Thank you for purchasing a ticket at JSCinema!', 0, 1, 'C');

$pdf->Output();
?>
