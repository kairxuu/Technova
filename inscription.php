<?php
session_start();
require_once "db/db.php";

// Vérifier si le formulaire a été soumis
if (isset($_GET['identifiant'])) {
    // Récupération des données
    $identifiant = trim($_GET['identifiant']);
    $mdp = $_GET['mdp'];
    $prenom = isset($_GET['prenom']) ? trim($_GET['prenom']) : '';
    $nom = isset($_GET['nom']) ? trim($_GET['nom']) : '';
    $mail = trim($_GET['mail']);
    $tel = trim($_GET['telephone']);

    // Vérification des champs obligatoires et du format de l'email
    if (!empty($identifiant) && !empty($mdp) && !empty($mail) && !empty($tel) && 
        filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        
        // Vérification de l'utilisateur existant
        $check = $conn->query("SELECT ID_Client FROM client WHERE Identifiant = '$identifiant' OR Mail = '$mail'");
        if ($check->num_rows === 0) {
            // Hachage du mot de passe
            $mdp_hash = md5($mdp);

            // Insertion du nouvel utilisateur
            $sql = "INSERT INTO client (Identifiant, MDP, Prenom, Nom, Mail, Tel) 
                    VALUES ('$identifiant', '$mdp_hash', '$prenom', '$nom', '$mail', '$tel')";
            
            if ($conn->query($sql) === TRUE) {
                header("Location: connexion.php");
                exit();
            }
        }
    }
    
    // En cas d'échec, recharger la page
    header("Location: inscription.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
</head>

    <?php include 'components/header.php'; ?>

<body>
    <div class="background-wrapper"></div>
    
    <section class="formulaire-inscription">
        <div class="formulaire-inscription-container">
            <h2>Inscription</h2>
            <form class="form-group" method="GET" action="inscription.php">
                <label for="identifiant">Identifiant</label>
                <input type="text" id="identifiant" name="identifiant" placeholder="Identifiant" required>
                
                <label for="prenom">Prénom</label>
                <input type="text" id="prenom" name="prenom" placeholder="Prénom">
                
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="Nom">
                
                <label for="mdp">Mot de passe</label>
                <input type="password" id="MDP" name="mdp" placeholder="Mot de passe" required>
                
                <label for="mail">Adresse email</label>
                <input type="email" id="mail" name="mail" placeholder="Adresse email" required>
                
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" placeholder="Téléphone" required>
                
                <button type="submit" class="primary">S'inscrire</button>
            </form>
        </div>
    </section>
</body>
    <?php require 'components/footer.php'; ?>
</html>