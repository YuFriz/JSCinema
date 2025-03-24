<?php
require 'session_manager.php';

// Połączenie z bazą danych
$conn = new mysqli('localhost', 'root', '', 'cinemajs');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT imie, nazwisko, email, Status, profile_image FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found!");
}

$stmt->close();
$conn->close();

// Generowanie inicjałów
$firstInitial = strtoupper(substr($user['imie'], 0, 1));
$lastInitial = strtoupper(substr($user['nazwisko'], 0, 1));
$initials = $firstInitial . $lastInitial;

$profileImage = !empty($user['profile_image']) ? "uploads/" . htmlspecialchars($user['profile_image']) : "";
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<!-- Nawigacja -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
    </div>
</nav>

<!--  Kontener Profilu -->
<div class="container profile-container">
    <div class="profile-card">

        <!-- 🔹 Kontener na avatar + edycję -->
        <div class="profile-avatar-container">
            <div class="profile-avatar">
                <?php if ($profileImage): ?>
                    <img src="<?php echo $profileImage; ?>" alt="Profile Image" class="rounded-circle profile-image">
                <?php else: ?>
                    <div class="initials"><?php echo $initials; ?></div>
                <?php endif; ?>
            </div>

            <!-- 🔹 Ikona edycji (pióro) PRZENIESIONA POZA KOŁO -->
            <span class="edit-icon" onclick="document.getElementById('profileImageInput').click();">
                <i class="fa-solid fa-pen"></i>
            </span>
        </div>


        <!-- 🔹 Ukryty input do przesyłania pliku -->
        <form action="upload_profile_image.php" method="post" enctype="multipart/form-data" id="imageUploadForm" class="d-none">
            <input type="file" name="profile_image" id="profileImageInput" accept="image/*">
        </form>

        <!-- 🔹 Dane użytkownika -->
        <h3 class="fw-bold mt-3"><?php echo htmlspecialchars($user['imie'] . " " . $user['nazwisko']); ?></h3>


        <?php if ($user['Status'] == 'user'): ?>
            <p class="text-muted"><i class="fa-solid fa-star"></i> JSCinema club member</p>


        <?php else: ?>
            <p class="text-muted"><i class="fa-solid fa-shield-halved"></i> ADMIN</p>

        <?php endif; ?>



        <!-- 🔹 Przyciski użytkownika -->
        <div class="d-grid gap-3 col-md-8 mx-auto">
            <?php if ($user['Status'] == 'admin'): ?>
                <a href="analysis.php" class="btn btn-secondary">
                    <i class="fa-solid fa-chart-line"></i> Analysis
                </a>
                <a href="movie_admin.php" class="btn btn-secondary">
                    <i class="fa-solid fa-film"></i> Movies
                </a>
                <a href="auditorium_repertuar.php" class="btn btn-secondary">
                    <i class="fa-solid fa-chair"></i> Auditorium & Repertuar
                </a>
                <a href="reviews_and_ratings_admin.php" class="btn btn-secondary">
                    <i class="fa-solid fa-star-half-stroke"></i> Review
                </a>
                <a href="users-admin.php" class="btn btn-secondary">
                    <i class="fa-solid fa-users"></i> Users
                </a>

            <?php else: ?>
                <a href="my_tickets.php" class="btn btn-primary">
                    <i class="fa-solid fa-ticket"></i> My Tickets
                </a>
                <a href="reviews_and_ratings.php" class="btn btn-primary">
                    <i class="fa-solid fa-star-half-stroke"></i> Reviews and Ratings
                </a>
                <a href="settings.php" class="btn btn-outline-dark">
                    <i class="fa-solid fa-gear"></i> Settings
                </a>

            <?php endif; ?>
        </div>

        <!-- 🔹 Przycisk wylogowania -->
        <a href="logout.php" class="btn btn-danger mt-3">Log Out</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('profileImageInput').addEventListener('change', function() {
        document.getElementById('imageUploadForm').submit();
    });
</script>
</body>
</html>
