<?php
session_start();

// Funkcja zarządzająca sesją z limitem czasu

/*
30 sec
manageSession($session_lifetime = 30
manageCookieSession($cookie_lifetime = 30)


$session_lifetime = 30;
$cookie_lifetime = 30;  


*/

function manageSession($session_lifetime = 86400) {
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime)) {
        session_unset(); // Usuwa dane sesji
        session_destroy(); // Niszczy sesję
        return false; // Sesja wygasła
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // Aktualizacja znacznika czasu ostatniej aktywności
    return true; // Sesja aktywna
}

// Funkcja zarządzająca ciasteczkami
function manageCookieSession($cookie_lifetime = 86400) {
    if (!isset($_COOKIE['session_active'])) {
        // Tworzymy ciasteczko, jeśli nie istnieje
        setcookie('session_active', time(), time() + $cookie_lifetime, "/");
    } else {
        $cookie_time = intval($_COOKIE['session_active']); // Konwersja na liczbę całkowitą
        if (time() > ($cookie_time + $cookie_lifetime)) {
            // Jeśli czas życia ciasteczka upłynął
            session_unset();
            session_destroy();
            setcookie('session_active', '', time() - 3600, "/"); // Usuwamy ciasteczko
            return false; // Sesja wygasła
        } else {
            // Odświeżamy ciasteczko
            setcookie('session_active', time(), time() + $cookie_lifetime, "/");
        }
    }
    return true; // Sesja aktywna
}

// Wywołanie obu metod
$session_lifetime = 86400;
$cookie_lifetime = 86400;

$session_active = manageSession($session_lifetime);
$cookie_active = manageCookieSession($cookie_lifetime);

// Jeśli sesja wygasła, przekieruj użytkownika np. na stronę logowania
if (!$session_active || !$cookie_active) {
    header("Location: index.php");
    exit();
}
?>
