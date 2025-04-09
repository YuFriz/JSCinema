<?php
require 'session_manager.php';
require 'db_connection.php';

// Sprawdzenie uprawnień administratora
$user_id = $_SESSION['user_id'];
$sql = "SELECT Status FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['Status'] !== 'admin') {
    die("Access denied! Only admins can add screenings.");
}
$stmt->close();

// AJAX: Pobieranie filmów ze statusem "already showing"
if (isset($_GET['date'])) {
    $movies_query = $conn->prepare("
        SELECT id, name FROM movies 
        WHERE status = 'already showing' 
        ORDER BY name ASC
    ");
    $movies_query->execute();
    $movies_result = $movies_query->get_result();

    $movies = [];
    while ($row = $movies_result->fetch_assoc()) {
        $movies[] = $row;
    }

    echo json_encode($movies);
    exit;
}

// AJAX: Pobieranie dostępnych audytoriów
if (isset($_GET['movie_id'], $_GET['screening_date'], $_GET['start_time'])) {
    $movie_id = $_GET['movie_id'];
    $screening_date = $_GET['screening_date'];
    $start_time = $_GET['start_time'];

    $sql = "SELECT id, name FROM auditoriums 
            WHERE id NOT IN (
                SELECT auditorium_id FROM screenings 
                WHERE screening_date = ? AND start_time = ?
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $screening_date, $start_time);
    $stmt->execute();
    $result = $stmt->get_result();

    $auditoriums = [];
    while ($row = $result->fetch_assoc()) {
        $auditoriums[] = $row;
    }

    echo json_encode($auditoriums);
    exit;
}

// Domyślne pobieranie filmów ze statusem "already showing"
$movies_query = $conn->prepare("
    SELECT id, name FROM movies 
    WHERE status = 'already showing' 
    ORDER BY name ASC
");
$movies_query->execute();
$movies = $movies_query->get_result();

// Obsługa formularza
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $movie_id = $_POST['movie_id'] ?? null;
    $screening_date = $_POST['screening_date'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $auditorium_id = $_POST['auditorium_id'] ?? null;

    if (!$movie_id || !$screening_date || !$start_time || !$auditorium_id) {
        $message = "<div class='alert alert-danger'>Błąd: Wszystkie pola są wymagane!</div>";
    } else {
        $sql_check = "SELECT id FROM screenings WHERE screening_date = ? AND start_time = ? AND auditorium_id = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("ssi", $screening_date, $start_time, $auditorium_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "<div class='alert alert-warning'>Błąd: W tym audytorium o tej godzinie już odbywa się seans!</div>";
        } else {
            $sql_insert = "INSERT INTO screenings (movie_id, screening_date, start_time, auditorium_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_insert);
            $stmt->bind_param("issi", $movie_id, $screening_date, $start_time, $auditorium_id);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Sukces: Seans został dodany!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Błąd: Nie udało się dodać seansu.</div>";
            }
        }
        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add screening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <a href="auditorium_repertuar.php" class="btn btn-outline-light">Back</a>
    </div>
</nav>

<div class="container mt-4">
    <h2>Add new screening</h2>

    <?php if (isset($message)) echo $message; ?>

    <form method="post">
        <div class="mb-3">
            <label for="movie_id" class="form-label">Movie</label>
            <select class="form-select" id="movie_id" name="movie_id" required>
                <option value="">-- Choose movie --</option>
                <?php while ($row = $movies->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="screening_date" class="form-label">Choose date</label>
            <input type="date" class="form-control" id="screening_date" name="screening_date" required>
        </div>

        <div class="mb-3 time-container d-none">
            <label class="form-label">Choose hour</label>
            <select class="form-select" id="selected_time" name="start_time" required></select>
        </div>

        <div class="mb-3 auditorium-container d-none">
            <label class="form-label">Choose auditorium</label>
            <div class="auditorium-buttons"></div>
            <input type="hidden" name="auditorium_id" id="selected_auditorium" required>
        </div>

        <button type="submit" class="btn btn-success">Add</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const movieSelect = document.getElementById("movie_id");
        const screeningDate = document.getElementById("screening_date");
        const timeContainer = document.querySelector(".time-container");
        const timeSelect = document.getElementById("selected_time");
        const auditoriumContainer = document.querySelector(".auditorium-container");
        const auditoriumButtonsContainer = document.querySelector(".auditorium-buttons");
        const selectedAuditoriumInput = document.getElementById("selected_auditorium");
        const addButton = document.querySelector("button[type='submit']"); // Przycisk Add
        const form = document.querySelector("form"); // Formularz

        function generateTimeOptions() {
            timeSelect.innerHTML = '<option value="">-- Choose hour --</option>';
            for (let hour = 9; hour <= 20; hour++) {
                for (let min = 0; min < 60; min += 30) {
                    let time = `${hour.toString().padStart(2, "0")}:${min.toString().padStart(2, "0")}:00`;
                    let option = document.createElement("option");
                    option.value = time;
                    option.textContent = time.slice(0, -3);
                    timeSelect.appendChild(option);
                }
            }
            timeContainer.classList.remove("d-none");
        }

        function fetchAuditoriums() {
            fetch(`check_availability.php?movie_id=${movieSelect.value}&screening_date=${screeningDate.value}&start_time=${timeSelect.value}`)
                .then(response => response.json())
                .then(data => {
                    auditoriumButtonsContainer.innerHTML = "";
                    if (data.auditoriums.length) {
                        data.auditoriums.forEach(aud => {
                            let btn = document.createElement("button");
                            btn.classList.add("btn", "m-1");
                            btn.textContent = aud.name;
                            btn.dataset.id = aud.id;

                            if (aud.occupied) {
                                btn.classList.add("btn-danger");
                                btn.disabled = true;
                            } else {
                                btn.classList.add("btn-outline-primary");
                                btn.onclick = function (event) {
                                    event.preventDefault(); // Zatrzymanie domyślnego działania przycisku
                                    document.querySelectorAll(".auditorium-buttons button").forEach(b => b.classList.remove("btn-primary", "selected"));
                                    btn.classList.add("btn-primary", "selected");
                                    selectedAuditoriumInput.value = btn.dataset.id; // Teraz wartość zapisuje się, ale nie wysyła formularza
                                };
                            }

                            auditoriumButtonsContainer.appendChild(btn);
                        });
                        auditoriumContainer.classList.remove("d-none");
                    }
                })
                .catch(error => console.error("Błąd pobierania audytoriów:", error));
        }

        // Zapobieganie wysyłaniu formularza bez wybranego audytorium
        form.addEventListener("submit", function (event) {
            if (!selectedAuditoriumInput.value) {
                event.preventDefault(); // Zatrzymanie wysyłania formularza
                alert("Wybierz audytorium przed dodaniem seansu!");
            }
        });

        screeningDate.addEventListener("change", generateTimeOptions);
        timeSelect.addEventListener("change", function () {
            if (timeSelect.value !== "") {
                fetchAuditoriums();
            }
        });
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dateInput = document.getElementById("screening_date");

        dateInput.addEventListener("click", function () {
            this.showPicker?.(); // nowoczesne przeglądarki (np. Chrome)
        });
    });
</script>


</body>
</html>
