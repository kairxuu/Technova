<?php
// Configuration de la connexion à la base de données
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Remplacez par votre nom d'utilisateur MySQL
define('DB_PASSWORD', 'root');   // Remplacez par votre mot de passe MySQL
define('DB_NAME', 'technova');   // Remplacez par le nom de votre base de données

// Connexion à la base de données MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérification de la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter à la base de données. " . mysqli_connect_error());
}
?>
