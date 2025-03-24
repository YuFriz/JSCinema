<?php
require 'db_connection.php';
require 'session_manager.php';

// Sprawdzenie, czy administrator
$admin_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin || $admin['Status'] !== 'admin') {
    die("Brak dostępu. Tylko administratorzy mogą edytować recenzje.");
}

// Pobranie ID recenzji
if (!isset($_GET['id'])) {
    die("Brak ID recenzji.");
}
$review_id = intval($_GET['id']);

// Pobierz dane recenzji
$sql = "SELECT r.review, r.star, u.imie, u.nazwisko, m.name AS movie_name
        FROM reviews_ratings r
        JOIN users u ON r.user_id = u.id
        JOIN movies m ON r.movie_id = m.id
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

if (!$review) {
    die("Recenzja nie istnieje.");
}

// Zaktualizuj recenzję
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_review = $_POST['review'];
    $new_star = intval($_POST['star']);

    $update_stmt = $conn->prepare("UPDATE reviews_ratings SET review = ?, star = ? WHERE id = ?");
    $update_stmt->bind_param("sii", $new_review, $new_star, $review_id);

    if ($update_stmt->execute()) {
        $update_stmt->close();
        header("Location: reviews_and_ratings_admin.php?updated=1");
        exit();
    } else {
        echo "Błąd podczas aktualizacji.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edit Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h3 class="mb-3">Edit Review for "<?php echo htmlspecialchars($review['movie_name']); ?>"</h3>
    <p><strong>User:</strong> <?php echo htmlspecialchars($review['imie'] . " " . $review['nazwisko']); ?></p>

    <form method="post">
        <div class="mb-3">
            <label for="review" class="form-label">Review</label>
            <textarea class="form-control" id="review" name="review" rows="5" required><?php echo htmlspecialchars($review['review']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="star" class="form-label">Stars (1-5)</label>
            <select class="form-select" id="star" name="star" required>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php if ($i == $review['star']) echo 'selected'; ?>>
                        <?php echo $i; ?> ⭐
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="reviews_and_ratings_admin.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>

<?php $conn->close(); ?>
