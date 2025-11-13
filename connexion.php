<?php
/**
 * Fichier : connexion.php
 * Description : Gère l'authentification des utilisateurs
 */

// Démarrage de la session pour gérer les variables de session
session_start();

// Inclusion du fichier de connexion à la base de données
require 'db/db.php';

// Vérification si le formulaire de connexion a été soumis (méthode GET)
if (isset($_GET['email'])) {
    // Nettoyage de l'email (suppression des espaces en début/fin)
    $email = trim($_GET['email']);
    // Récupération du mot de passe (sans nettoyage pour ne pas altérer le hash)
    $password = $_GET['password'];

    // Validation des données du formulaire
    if (!empty($email) && !empty($password) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Hachage du mot de passe pour comparaison avec la base de données
        // Note : L'utilisation de md5 n'est pas sécurisée en production
        // Il serait préférable d'utiliser password_hash() et password_verify()
        $hashed_password = md5($password);
        
        // Préparation et exécution de la requête SQL pour vérifier les identifiants
        // Attention : Cette requête est vulnérable aux injections SQL
        // En production, utilisez des requêtes préparées
        $check = $conn->query("SELECT ID_Client FROM client WHERE Mail = '$email' AND MDP = '$hashed_password'");
        
        // Vérification si un utilisateur correspond aux identifiants fournis
        if ($check->num_rows > 0) {
            // Récupération des données de l'utilisateur
            $user = $check->fetch_assoc();
            
            // Stockage de l'ID de l'utilisateur dans la session
            $_SESSION['user_id'] = $user['ID_Client'];
            
            // Récupération du nom d'utilisateur pour l'affichage
            $result = $conn->query("SELECT Identifiant FROM client WHERE ID_Client = " . $user['ID_Client']);
            if ($result && $result->num_rows > 0) {
                $user_data = $result->fetch_assoc();
                $_SESSION['username'] = $user_data['Identifiant'];
            }
            
            // Gestion de la redirection après connexion réussie
            // Si une URL de redirection est enregistrée dans la session, on l'utilise
            // Sinon, on redirige vers la page d'accueil
            $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
            unset($_SESSION['redirect_after_login']); // Nettoyage de la variable de session
            
            // Redirection vers la page appropriée
            header("Location: $redirect");
            exit(); // Arrêt de l'exécution du script après la redirection
        }
    }
    
    // Si on arrive ici, c'est que la connexion a échoué
    // On redirige vers la page de connexion avec un message d'erreur
    $_SESSION['error'] = "Identifiants incorrects";
    header("Location: connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
    <?php include 'components/header.php'; ?>
</head>
<body>
    <div class="background-wrapper"></div>
    <section class="formulaire-connexion">
        <div class="formulaire-container">

            <?php if (empty($success)): ?>
                <h2>Connexion</h2>
                <form class="form-group" method="GET" action="connexion.php">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" placeholder="Adresse email" required>
                    
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                    
                    <button type="submit" class="primary">Se connecter</button>
                    <div class="form-footer">
                        <a href="inscription.php">Pas de compte ? Inscrivez-vous</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
    <?php require 'components/footer.php'; ?>
</body>
</html>