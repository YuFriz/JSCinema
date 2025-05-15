<?php
require 'db_connection.php';
require_once('vendor/setasign/fpdf/fpdf.php');

if (empty($_GET['ids'])) {
    echo "<h2 style='color: red; text-align: center; margin-top: 50px;'>Ticket data not found. Please return to your order summary.</h2>";
    exit();
}

function is_mobile() {
    return preg_match('/Android|iPhone|iPad|iPod|Opera Mini|IEMobile|Mobile/i', $_SERVER['HTTP_USER_AGENT']);
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
$screening = $tickets[0]; // Zakładamy, że wszystkie bilety dotyczą tego samego seansu

if (!is_mobile()) {
    if (ob_get_length()) ob_end_clean();

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, "Cinema Ticket - JSCinema", 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 10, "Movie: " . utf8_decode($screening['movie_name']), 0, 1);
    $pdf->Cell(190, 10, "Date: " . $screening['screening_date'], 0, 1);
    $pdf->Cell(190, 10, "Time: " . $screening['start_time'], 0, 1);
    $pdf->Cell(190, 10, "Auditorium: " . utf8_decode($screening['auditorium_name']), 0, 1);
    $pdf->Ln(5);

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

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="ticket_' . implode('_', $ticket_ids) . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    $pdf->Output('I');
    exit();
} else {
    $time = date("H:i", strtotime($screening['start_time']));
    $download_link = $_SERVER['PHP_SELF'] . '?ids=' . urlencode($_GET['ids']);

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
    <p><strong>Movie:</strong> {$screening['movie_name']}</p>
    <p><strong>Date:</strong> {$screening['screening_date']}</p>
    <p><strong>Time:</strong> {$time}</p>
    <p><strong>Auditorium:</strong> {$screening['auditorium_name']}</p>
    <table>
      <thead>
        <tr><th>Seat</th><th>Ticket Type</th><th>Price</th></tr>
      </thead>
      <tbody>
HTML;

    foreach ($tickets as $ticket) {
        $seat = "Row {$ticket['row_number']}, Seat {$ticket['seat_number']}";
        $type = ucfirst($ticket['ticket_type']);
        $price = number_format($ticket['price'], 2) . " \$";

        echo "<tr><td>{$seat}</td><td>{$type}</td><td>{$price}</td></tr>";
    }

    echo <<<HTML
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
