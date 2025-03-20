<?php
// Rozpoczęcie sesji
session_start();

// Usuwanie danych sesji
session_unset(); 

// Niszczenie sesji
session_destroy();

// Usunięcie ciasteczka sesji (jeśli istnieje)
setcookie('session_active', '', time() - 3600, '/');

// Przekierowanie użytkownika na stronę główną lub stronę logowania
header("Location: index.php");
exit();
?>
