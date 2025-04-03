<?php
session_start();

$movie_id = $_GET['movie_id'] ?? null;
$screening_id = $_GET['screening_id'] ?? null;
$movie_name = $_GET['movie_name'] ?? '';
$start_time = $_GET['start_time'] ?? '';

if (!$movie_id || !$screening_id) {
    die("Error: Missing required data.");
}

if (isset($_SESSION['user_id'])) {
    header("Location: buy_ticket.php?movie_id=$movie_id&screening_id=$screening_id&movie_name=" . urlencode($movie_name) . "&start_time=" . urlencode($start_time));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Action</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .option-card {
            max-width: 500px;
            margin: auto;
            margin-top: 10%;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .option-card h2 {
            margin-bottom: 20px;
        }
        .btn-custom {
            width: 100%;
            margin-bottom: 15px;
            padding: 12px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="option-card text-center">
    <h2><i class="bi bi-person-circle me-2"></i>Select an Option</h2>
    <p>To continue, please choose whether to log in or proceed as a guest.</p>

    <a href="register_login.php?movie_id=<?php echo $movie_id; ?>&screening_id=<?php echo $screening_id; ?>&movie_name=<?php echo urlencode($movie_name); ?>&start_time=<?php echo urlencode($start_time); ?>" class="btn btn-primary btn-custom">
        <i class="bi bi-box-arrow-in-right me-2"></i> Log In / Register
    </a>

    <a href="buy_ticket.php?movie_id=<?php echo $movie_id; ?>&screening_id=<?php echo $screening_id; ?>&movie_name=<?php echo urlencode($movie_name); ?>&start_time=<?php echo urlencode($start_time); ?>" class="btn btn-outline-secondary btn-custom">
        <i class="bi bi-person me-2"></i> Continue as Guest
    </a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
