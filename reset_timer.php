<?php
session_start();

// Usuwamy zmienne związane z timerem
unset($_SESSION['reservation_timer_start']);
unset($_SESSION['timer_screening_id']);

// Można wyczyścić także wybrane bilety, jeśli chcesz:
unset($_SESSION['selected_tickets']);

header("Location: repertoires.php");
exit;
