<?php
session_start();

// Pobranie nazwy pliku z parametru GET
$file_name = $_GET['file'] ?? '';
$file_path = __DIR__ . "/tickets/" . $file_name;

// Sprawdzenie, czy plik istnieje
if (!$file_name || !file_exists($file_path)) {
    die("Błąd: Plik nie istnieje.");
}

// Ustawienia nagłówków do pobrania pliku
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));
readfile($file_path);
exit();
?>
