<?php
require 'session_manager.php';
require 'db_connection.php';

// Zakładając, że masz dane użytkownika w sesji
$user_id = $_SESSION['user_id'];

// Pobieranie danych użytkownika
$sql = "SELECT imie, nazwisko, email, data_urodzenia FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<div class='alert alert-danger text-center'>User not found!</div>";
    exit;
}

$success_message = "";
$error_message = "";

// Aktualizacja danych po przesłaniu formularza
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_imie = $_POST['imie'];
    $new_nazwisko = $_POST['nazwisko'];
    $new_data_urodzenia = $_POST['data_urodzenia'];

    if (!empty($new_password)) {
        $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE users SET email = ?, password = ?, imie = ?, nazwisko = ?, data_urodzenia = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssssi", $new_email, $new_password_hashed, $new_imie, $new_nazwisko, $new_data_urodzenia, $user_id);
    } else {
        $sql_update = "UPDATE users SET email = ?, imie = ?, nazwisko = ?, data_urodzenia = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ssssi", $new_email, $new_imie, $new_nazwisko, $new_data_urodzenia, $user_id);
    }

    if ($stmt->execute()) {
        $success_message = "Settings updated successfully!";
    } else {
        $error_message = "Error updating settings: " . $conn->error;
    }
    $stmt->close();
}

// Odśwież dane po aktualizacji
$sql = "SELECT imie, nazwisko, email, data_urodzenia FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="custom_styles.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="profile.php" class="btn btn-outline-light">Back to Profile Panel</a>
    </div>
</nav>


<div class="container mt-5">
    <div class="card mx-auto shadow-lg settings-card">
        <div class="card-header bg-primary text-white text-center">
            <h3>Account Settings</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success text-center"><?= $success_message ?></div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-danger text-center"><?= $error_message ?></div>
            <?php endif; ?>

            <form action="settings.php" method="POST">
                <div class="mb-3">
                    <label for="imie" class="form-label">First Name:</label>
                    <input type="text" id="imie" name="imie" class="form-control" value="<?= htmlspecialchars($user['imie']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="nazwisko" class="form-label">Last Name:</label>
                    <input type="text" id="nazwisko" name="nazwisko" class="form-control" value="<?= htmlspecialchars($user['nazwisko']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="data_urodzenia" class="form-label">Date of Birth:</label>
                    <input type="date" id="data_urodzenia" name="data_urodzenia" class="form-control" value="<?= htmlspecialchars($user['data_urodzenia']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password:</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password (optional)">
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success">Update Settings</button>
                    <a href="profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>

        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
