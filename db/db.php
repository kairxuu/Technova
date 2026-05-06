<?php
// --- BASE DE DONNÉES ---
// Définit les constantes de connexion et crée la variable $conn utilisée partout dans le projet.

define("HOST", "localhost"); // Adresse du serveur MySQL
define("USER", "root");      // Nom d'utilisateur MySQL
define("MDP",  "root");      // Mot de passe MySQL
define("DB",   "bts1aurlom"); // Nom de la base de données

// Ouvre la connexion à la base de données
$conn = mysqli_connect(HOST, USER, MDP, DB);

// Arrête le script si la connexion échoue
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8'); // Force l'encodage UTF-8 pour éviter les problèmes d'accents
date_default_timezone_set('Europe/Paris'); // Définit le fuseau horaire du serveur
?>