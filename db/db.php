<?php

define("HOST", "localhost");     // Adresse du serveur MySQL
define("USER", "root");          // Nom d'utilisateur MySQL
define("MDP", "root");           // Mot de passe MySQL
define("DB", "bts1aurlom");     // Nom de la base de données

$conn = mysqli_connect(HOST, USER, MDP, DB);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Définition du jeu de caractères en UTF-8 pour éviter les problèmes d'encodage
mysqli_set_charset($conn, 'utf8');

// Définition du fuseau horaire (optionnel mais recommandé)
date_default_timezone_set('Europe/Paris');
?>