<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$baza   = 'moja_strona';
$login = 'admin';
$pass = 'password';

// Połączenie z bazą
$link = mysqli_connect($dbhost, $dbuser, $dbpass, $baza);

// Sprawdzenie połączenia
if (!$link) {
    die('<b>Przerwane połączenie z bazą danych</b>');
}
?>
