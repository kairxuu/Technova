<?php

define("HOST", "localhost");
define("USER", "root");
define("MDP",  "root");
define("DB",   "bts1aurlom");

$conn = mysqli_connect(HOST, USER, MDP, DB);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8'); // Encodage UTF-8
date_default_timezone_set('Europe/Paris');
?>