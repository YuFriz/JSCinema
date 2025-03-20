<?php
    require 'session_manager.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
        <div class="container">
            <nav>
                <ul>
                    <li><h1><a href="index.php">JSCinema</a></h1></li>
                </ul>
            </nav>

            <nav>
                <ul>
                    <li><a href="aboutCinema.php">About</a></li>
                    <li><a href="repertoires.php">Repertoires</a></li>
                    <li><a href="movies.php">Movies</a></li>   
                </ul>
            </nav>

            <nav>
                <ul>
                    <li>
                        <form action="search_movies.php" method="GET">
                        <input type="text" name="query" placeholder="Search...">
                        <button type="submit">Search</button>
                        </form>
                    </li>
                    <?php

                        // Sprawdzamy, czy użytkownik jest zalogowany
                            if (isset($_SESSION['user_id'])) {
                            // Jeśli użytkownik jest zalogowany, możesz wyświetlić link do profilu
                            echo '<ul><li><a href="profile.php">Profile</a></li></ul>';
                        } else {
                            // Jeśli użytkownik nie jest zalogowany, możesz wyświetlić inne opcje
                            echo '<ul><li><a href="register_login.php">Login/Register</a></li></ul>';
                        }
                        ?>

                    
                </ul>
            </nav>
        </div>
    </header>

<?php
// Połączenie z bazą danych
$host = 'localhost';
$dbname = 'cinema';
$username = 'root';
$password = '';
$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

// Pobranie identyfikatora filmu z URL
$movie_id = $_GET['movie_id'] ?? null;

// Sprawdzenie, czy movie_id jest ustawione
if (!$movie_id) {
    echo "<p>Błąd: Nie wybrano filmu.</p>";
    exit;
}

// Pobranie szczegółów filmu i dat screeningu z bazy danych
$query = "SELECT m.name, m.description, m.img1, s.screening_date, s.start_time, s.auditorium
          FROM movies m
          JOIN screenings s ON m.id = s.movie_id
          WHERE m.id = :movie_id
          ORDER BY s.screening_date, s.start_time";
$stmt = $conn->prepare($query);
$stmt->execute(['movie_id' => $movie_id]);
$screenings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Wyświetlenie wyników
?>


<?php if ($screenings): ?>
    <h2>Film: <?php echo htmlspecialchars($screenings[0]['name']); ?></h2>
    <img src="<?php echo htmlspecialchars($screenings[0]['img1']); ?>" alt="<?php echo htmlspecialchars($screenings[0]['name']); ?>" width="200">
    <p><?php echo htmlspecialchars($screenings[0]['description']); ?></p>

    <h3>Dostępne daty i godziny:</h3>
    <ul>
        <?php foreach ($screenings as $screening): ?>
            <li>
                Data: <?php echo htmlspecialchars($screening['screening_date']); ?> |
                Godzina: <?php echo htmlspecialchars($screening['start_time']); ?> |
                Sala: <?php echo htmlspecialchars($screening['auditorium']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Brak dostępnych screeningów dla tego filmu.</p>
<?php endif; ?>

</body>
</html>
