<?php
session_start();

// Pobranie danych filmu z GET
$movie_id = $_GET['movie_id'] ?? null;
$screening_id = $_GET['screening_id'] ?? null;
$movie_name = $_GET['movie_name'] ?? '';
$start_time = $_GET['start_time'] ?? '';

// Sprawdzenie, czy wszystkie wymagane dane są dostępne
if (!$movie_id || !$screening_id) {
    die("Błąd: Brak wymaganych danych.");
}

// Jeśli użytkownik jest zalogowany, przekieruj go bezpośrednio do buy_ticket.php
if (isset($_SESSION['user_id'])) {
    header("Location: buy_ticket.php?movie_id=$movie_id&screening_id=$screening_id&movie_name=" . urlencode($movie_name) . "&start_time=" . urlencode($start_time));
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wybierz opcję</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Wybierz opcję</h2>
    <p>Aby kontynuować, musisz wybrać, czy chcesz się zalogować, czy kontynuować jako gość.</p>

    <a href="register_login.php?movie_id=<?php echo $movie_id; ?>&screening_id=<?php echo $screening_id; ?>&movie_name=<?php echo urlencode($movie_name); ?>&start_time=<?php echo urlencode($start_time); ?>" class="button">Zaloguj się</a>
    <a href="buy_ticket.php?movie_id=<?php echo $movie_id; ?>&screening_id=<?php echo $screening_id; ?>&movie_name=<?php echo urlencode($movie_name); ?>&start_time=<?php echo urlencode($start_time); ?>" class="button">Kontynuuj jako gość</a>
</div>

</body>
</html>
