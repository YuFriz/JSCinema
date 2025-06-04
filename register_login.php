<?php
global $conn;
session_start();
require 'db_connection.php';

$form_type = 'login'; // domyślnie login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $form_type = 'register';
    } elseif (isset($_POST['login'])) {
        $form_type = 'login';
    }
}


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
    $repeat_password = $_POST['repeat_password'];
    $data_urodzenia = $_POST['data_urodzenia'];

    $sql_check = "SELECT * FROM users WHERE email = '$email'";
    $result_check = $conn->query($sql_check);


if ($password !== $repeat_password) {
        $message = "Passwords do not match.";
    } else {
        $sql_check = "SELECT * FROM users WHERE email = '$email'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            $message = "E-mail is already in use.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, imie, nazwisko, password, data_urodzenia, status, created_at) 
                    VALUES ('$email', '$imie', '$nazwisko', '$hashed_password', '$data_urodzenia', 'user', NOW())";

            if ($conn->query($sql) === TRUE) {
                $_SESSION['user_id'] = $conn->insert_id;
                header("Location: $redirect_url");
                exit();
            } else {
                $message = "Registration error: " . $conn->error;
            }
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
            $message = "Wrong password.";
        }
    } else {
        $message = "User doesn't exist.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSCinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="reg_log">

<!-- 🔹 Nawigacja -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">JSCinema</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="aboutCinema.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="repertoires.php">Repertoires</a></li>
                <li class="nav-item"><a class="nav-link" href="movies.php">Movies</a></li>
                <li class="nav-item">
                    <form class="d-flex position-relative">
                        <input class="form-control" id="myInput" type="text" placeholder="Search movies..." autocomplete="off">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                        <div id="search-results" class="position-absolute w-100 bg-white shadow rounded"></div>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>



<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="welcomeRegLog-card text-center p-4 shadow-sm">
                <h2>Welcome to JSCinema</h2>
                <p class="lead mb-0">Create an account or log in to get exclusive discounts on movie tickets!</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="auth-card text-center p-4 shadow">
                <div class="toggle-buttons d-flex justify-content-center mb-4">
                    <button id="loginToggle" class="btn-toggle active">Login</button>
                    <button id="registerToggle" class="btn-toggle">Signup</button>
                </div>

                <!-- Login form -->
                <form id="loginForm" method="POST" action="register_login.php">
                    <input type="email" name="email_login" placeholder="Email Address" class="form-control mb-3" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <input type="password" name="password_login" placeholder="Password" class="form-control mb-3" required>
                    <div class="text-start mb-3">
                    </div>
                    <button type="submit" name="login" class="btn gradient-btn w-100">Login</button>
                </form>

                <!-- Register form -->
                <form id="registerForm" method="POST" action="register_login.php" class="d-none">
                    <input type="email" name="email" placeholder="Email Address" class="form-control mb-3" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <input type="text" name="imie" placeholder="Name" class="form-control mb-3" required value="<?= isset($_POST['imie']) ? htmlspecialchars($_POST['imie']) : '' ?>">
                    <input type="text" name="nazwisko" placeholder="Surname" class="form-control mb-3" required value="<?= isset($_POST['nazwisko']) ? htmlspecialchars($_POST['nazwisko']) : '' ?>">
                    <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>
                    <input type="password" name="repeat_password" placeholder="Repeat Password" class="form-control mb-3" required>
                    <input type="date" name="data_urodzenia" class="form-control mb-3" required value="<?= isset($_POST['data_urodzenia']) ? htmlspecialchars($_POST['data_urodzenia']) : '' ?>">
                    <button type="submit" name="register" class="btn gradient-btn w-100">Register</button>
                </form>

            </div>
        </div>
    </div>
</div>


    <?php if (!empty($message)): ?>
        <div class="alert alert-warning text-center mt-4"><?php echo $message; ?></div>
    <?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById("loginToggle").addEventListener("click", function () {
        this.classList.add("active");
        document.getElementById("registerToggle").classList.remove("active");
        document.getElementById("loginForm").classList.remove("d-none");
        document.getElementById("registerForm").classList.add("d-none");
    });

    document.getElementById("registerToggle").addEventListener("click", function () {
        this.classList.add("active");
        document.getElementById("loginToggle").classList.remove("active");
        document.getElementById("registerForm").classList.remove("d-none");
        document.getElementById("loginForm").classList.add("d-none");
    });

</script>

<script>
    const formType = "<?= $form_type ?>";
    window.addEventListener("DOMContentLoaded", () => {
        if (formType === "register") {
            document.getElementById("registerToggle").click();
        } else {
            document.getElementById("loginToggle").click();
        }
    });
</script>


</body>
</html>
