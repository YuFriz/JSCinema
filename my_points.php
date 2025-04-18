<?php
require 'session_manager.php';
require 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT points, used_free_tickets FROM points WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$points = 0;
$used = 0;
$total_free_tickets = 0;
$points_to_next = 5;
$progress = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $points = $row['points'];
    $used = $row['used_free_tickets'];

    $total_free_tickets = floor($points / 5);
    $points_to_next = 5 - ($points % 5);
    $progress = ($points % 5) * 20;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Points</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="points">


<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Profile Panel</a>
    </div>
</nav>



<div class="container py-5">
    <div class="points-card shadow-lg p-4 mx-auto">
        <h2 class="text-center mb-4 text-warning fw-bold">User Points</h2>

            <div class="col-12">
                <div class="stat-box highlight-box text-center animate-pop">
                    <div class="big-icon bg-gradient">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div class="stat-title big-title">Current Points</div>
                    <div class="stat-value big-value"><?php echo $points; ?></div>
                </div>
            </div>
        <div class="row g-4 my-3">
            <div class="col-md-4">
                <div class="modern-card bg-gradient-red">
                    <div class="icon-bg"><i class="bi bi-ticket-perforated"></i></div>
                    <div class="modern-content">
                        <div class="modern-label">Used Free Tickets</div>
                        <div class="modern-value"><?php echo $used; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="modern-card bg-gradient-green">
                    <div class="icon-bg"><i class="bi bi-gift-fill"></i></div>
                    <div class="modern-content">
                        <div class="modern-label">Total Free Tickets</div>
                        <div class="modern-value"><?php echo $total_free_tickets; ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="modern-card bg-gradient-yellow">
                    <div class="icon-bg"><i class="bi bi-graph-up-arrow"></i></div>
                    <div class="modern-content">
                        <div class="modern-label">Points to Next Ticket</div>
                        <div class="modern-value"><?php echo $points_to_next; ?></div>
                    </div>
                </div>
            </div>
        </div>



        <div class="mb-3">
            <label class="form-label fw-semibold d-flex justify-content-between">
                <span>0</span>
                <span>5</span>
            </label>
            <div class="progress position-relative" style="height: 30px;" data-bs-toggle="tooltip" data-bs-placement="top" title="You have collected <?php echo $points % 5; ?>/5 points">
                <div class="progress-bar bg-warning text-dark fw-bold" role="progressbar"
                     style="width: <?php echo $progress; ?>%;"
                     aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>

        <p class="text-center text-muted fst-italic mt-3"><?php echo $progress; ?>% toward your next free ticket</p>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bootstrap tooltip init
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

</body>
</html>
