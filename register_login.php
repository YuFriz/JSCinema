<?php
global $conn;
session_start();
require 'db_connection.php';

if (isset($_GET['movie_id']) && isset($_GET['screening_id'])) {
    $_SESSION['movie_id'] = $_GET['movie_id'];
    $_SESSION['screening_id'] = $_GET['screening_id'];
    $_SESSION['movie_name'] = $_GET['movie_name'];
    $_SESSION['start_time'] = $_GET['start_time'];
}

$redirect_url = "profile.php";

if (isset($_SESSION['movie_id']) && isset($_SESSION['screening_id'])) {
    $redirect_url = "buy_ticket.php?movie_id={$_SESSION['movie_id']}&screening_id={$_SESSION['screening_id']}&movie_name=" . urlencode($_SESSION['movie_name']) . "&start_time=" . urlencode($_SESSION['start_time']);
}

if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $password = $_POST['password'];
    $data_urodzenia = $_POST['data_urodzenia'];

    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $message = "E-mail jest już używany.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, imie, nazwisko, password, data_urodzenia, status, created_at) 
                VALUES ('$email', '$imie', '$nazwisko', '$hashed_password', '$data_urodzenia', 'user', NOW())";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['user_id'] = $conn->insert_id;
            header("Location: $redirect_url");
            exit();
        } else {
            $message = "Błąd rejestracji: " . $conn->error;
        }
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email_login'];
    $password = $_POST['password_login'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: $redirect_url");
            exit();
        } else {
            $message = "Błędne hasło.";
        }
    } else {
        $message = "Użytkownik nie istnieje.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema</title>

    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<!-- 🔹 Nawigacja -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
    </div>
</nav>

<!-- 🔹 Kontener formularzy -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <!-- 🔹 Karta rejestracji -->
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4>Rejestracja</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="register_login.php">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" class="form-control mb-3" required>

                        <label for="imie">Imię:</label>
                        <input type="text" id="imie" name="imie" class="form-control mb-3" required>

                        <label for="nazwisko">Nazwisko:</label>
                        <input type="text" id="nazwisko" name="nazwisko" class="form-control mb-3" required>

                        <label for="password">Hasło:</label>
                        <input type="password" id="password" name="password" class="form-control mb-3" required>

                        <label for="data_urodzenia">Data urodzenia:</label>
                        <input type="date" id="data_urodzenia" name="data_urodzenia" class="form-control mb-3" required>

                        <button type="submit" name="register" class="btn btn-primary w-100">Zarejestruj się</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- 🔹 Karta logowania -->
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h4>Logowanie</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="register_login.php">
                        <label for="email_login">E-mail:</label>
                        <input type="email" id="email_login" name="email_login" class="form-control mb-3" required>

                        <label for="password_login">Hasło:</label>
                        <input type="password" id="password_login" name="password_login" class="form-control mb-3" required>

                        <button type="submit" name="login" class="btn btn-success w-100">Zaloguj się</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 Wiadomość o błędzie -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-warning text-center mt-4"><?php echo $message; ?></div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
