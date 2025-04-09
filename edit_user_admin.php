<?php
require 'session_manager.php';
require 'db_connection.php';

if (!isset($_GET['id'])) {
    die("Brak ID użytkownika.");
}

$user_id = intval($_GET['id']);

// Sprawdzenie, czy użytkownik to admin
$admin_id = $_SESSION['user_id'];
$check_sql = "SELECT Status FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $admin_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$admin = $check_result->fetch_assoc();

if (!$admin || $admin['Status'] !== 'admin') {
    die("Brak dostępu.");
}
$check_stmt->close();

// Aktualizacja użytkownika po przesłaniu formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_imie = $_POST['imie'];
    $new_nazwisko = $_POST['nazwisko'];
    $new_email = $_POST['email'];
    $new_status = $_POST['status'];
    $new_password = $_POST['password'];

    // Aktualizacja podstawowych danych
    $update_sql = "UPDATE users SET imie = ?, nazwisko = ?, email = ?, Status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $new_imie, $new_nazwisko, $new_email, $new_status, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Jeżeli podano nowe hasło, zaktualizuj je
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $pass_stmt->bind_param("si", $hashed_password, $user_id);
        $pass_stmt->execute();
        $pass_stmt->close();
    }

    header("Location: users-admin.php?success=1");
    exit();
}

// Pobieranie danych użytkownika
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Użytkownik nie istnieje.");
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="users-admin.php" class="btn btn-outline-light">Back</a>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Edit User: <?php echo htmlspecialchars($user['imie'] . " " . $user['nazwisko']); ?></h2>

    <form method="post">
        <div class="mb-3">
            <label for="imie" class="form-label">First Name</label>
            <input type="text" class="form-control" id="imie" name="imie" value="<?php echo htmlspecialchars($user['imie']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="nazwisko" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="nazwisko" name="nazwisko" value="<?php echo htmlspecialchars($user['nazwisko']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="user" <?php if ($user['Status'] === 'user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if ($user['Status'] === 'admin') echo 'selected'; ?>>Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New Password <small class="text-muted">(leave empty to keep current)</small></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password...">
        </div>

        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="users-admin.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>

<?php
$conn->close();
?>
