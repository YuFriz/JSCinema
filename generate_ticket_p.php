<?php
require 'session_manager.php';
require_once('vendor/setasign/fpdf/fpdf.php');
require 'db_connection.php';

if (!isset($_GET['ticket_id'])) {
    die("No ticket ID provided.");
}

function is_mobile() {
    return preg_match('/Android|iPhone|iPad|iPod|Opera Mini|IEMobile|Mobile/i', $_SERVER['HTTP_USER_AGENT']);
}

$ticket_id = intval($_GET['ticket_id']);
$force_download = isset($_GET['download']) && $_GET['download'] == '1';

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

if (!is_mobile() || $force_download) {
    if (ob_get_length()) ob_end_clean();

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

    header('Content-Type: application/pdf');

    $disposition = $force_download ? 'attachment' : 'inline';
    header("Content-Disposition: $disposition; filename=\"ticket_{$ticket_id}.pdf\"");
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    $pdf->Output();
    exit();

} else {
    $time = date("H:i", strtotime($ticket['start_time']));
    $formatted_price = number_format($ticket['price'], 2);

    $download_link = $_SERVER['PHP_SELF'] . '?ticket_id=' . urlencode($ticket_id) . '&download=1';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Ticket Summary - JSCinema</title>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 20px;
    background: #f5f7fa;
    color: #333;
  }
  h2 {
    text-align: center;
    color: #2c3e50;
  }
  .ticket-summary {
    max-width: 400px;
    margin: 20px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
  }
  .ticket-summary p {
    font-size: 1.1rem;
    margin: 8px 0;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
  }
  th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
  }
  th {
    background-color: #2980b9;
    color: white;
    font-weight: 600;
  }
  td {
    font-weight: 500;
  }
  .btn-download {
    display: block;
    width: 100%;
    margin-top: 25px;
    padding: 12px 0;
    background-color: #27ae60;
    color: white;
    text-align: center;
    font-weight: 600;
    font-size: 1.1rem;
    border-radius: 8px;
    text-decoration: none;
    transition: background-color 0.3s ease;
  }
  .btn-download:hover {
    background-color: #219150;
  }
</style>
</head>
<body>
  <div class="ticket-summary">
    <h2>Ticket Summary</h2>
    <p><strong>Movie:</strong> {$ticket['movie_name']}</p>
    <p><strong>Date:</strong> {$ticket['screening_date']}</p>
    <p><strong>Time:</strong> {$time}</p>
    <p><strong>Auditorium:</strong> Auditorium {$ticket['auditorium_id']}</p>
    <table>
      <thead>
        <tr><th>Seat</th><th>Ticket Type</th><th>Price</th></tr>
      </thead>
      <tbody>
        <tr>
          <td>Row {$ticket['row_number']}, Seat {$ticket['seat_number']}</td>
          <td>{$ticket['ticket_type']}</td>
          <td>{$formatted_price} $</td>
        </tr>
      </tbody>
    </table>
    <a href="{$download_link}" class="btn-download" download>Download PDF ticket</a>
  </div>
</body>
</html>
HTML;

    exit();

}
?>
