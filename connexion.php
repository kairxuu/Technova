<?php
/**
 * Fichier : connexion.php
 * 
 * Description :
 * Gère le processus d'authentification des utilisateurs.
 * Vérifie les identifiants, crée une session utilisateur et gère les redirections.
 * 
 * Points clés :
 * - Validation des entrées utilisateur
 * - Protection contre les injections SQL avec des requêtes préparées
 * - Gestion sécurisée des sessions
 * - Messages d'erreur clairs pour l'utilisateur
 */

// 1. Initialisation de la session avec des paramètres de sécurité
session_start([
    'cookie_httponly' => true,         // Empêche l'accès aux cookies via JavaScript
    'cookie_secure' => isset($_SERVER['HTTPS']), // Utilisation de HTTPS si disponible
    'cookie_samesite' => 'Strict',     // Protection contre les attaques CSRF
    'use_strict_mode' => true          // Mode strict pour les IDs de session
]);

// 2. Inclusion du fichier de connexion à la base de données
require_once 'db/db.php';

// 3. Vérification si le formulaire a été soumis (méthode POST recommandée pour les connexions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    // 4. Nettoyage et validation des données du formulaire
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Ne pas nettoyer le mot de passe pour ne pas altérer les caractères spéciaux
    
    // 5. Validation des champs obligatoires
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }
    
    // 6. Si pas d'erreurs de validation, on tente l'authentification
    if (empty($errors)) {
        try {
            // 7. Requête préparée pour éviter les injections SQL
            $query = "SELECT ID_Client, Identifiant, MDP FROM client WHERE Mail = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            
            if ($stmt === false) {
                throw new Exception("Erreur lors de la préparation de la requête");
            }
            
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // 8. Vérification si l'utilisateur existe
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // 9. Vérification du mot de passe
                // Note : Dans une version future, il faudrait utiliser password_verify() avec des mots de passe hashés avec password_hash()
                if (md5($password) === $user['MDP']) {
                    // 10. Authentification réussie - Mise à jour de la session
                    $_SESSION['user_id'] = $user['ID_Client'];
                    $_SESSION['username'] = $user['Identifiant'];
                    
                    // Régénération de l'ID de session pour prévenir les attaques de fixation de session
                    session_regenerate_id(true);
                    
                    // 11. Gestion de la redirection après connexion
                    $redirect = 'index.php'; // Page par défaut
                    
                    if (isset($_SESSION['redirect_after_login']) && 
                        strpos($_SESSION['redirect_after_login'], 'connexion.php') === false) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }
                    
                    // 12. Redirection sécurisée
                    header('Location: ' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'));
                    exit();
                }
            }
            
            // Si on arrive ici, l'authentification a échoué
            $errors[] = "Identifiants incorrects ou compte inexistant.";
            
        } catch (Exception $e) {
            // Journalisation de l'erreur (à remplacer par un système de logs en production)
            error_log('Erreur de connexion : ' . $e->getMessage());
            $errors[] = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
        }
    }
    
    // Si on arrive ici, il y a eu une erreur
    $_SESSION['error'] = implode('<br>', $errors);
    
    // On pré-remplit le formulaire avec l'email saisi (mais pas le mot de passe)
    $_SESSION['form_data'] = ['email' => $email];
    
    // Redirection vers la page de connexion
    header('Location: connexion.php');
    exit();
}

// Récupération des données du formulaire en cas d'erreur
$formData = $_SESSION['form_data'] ?? [];
$error = $_SESSION['error'] ?? '';

// Nettoyage des données de session après utilisation
unset($_SESSION['form_data'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de connexion à votre compte Technova">
    <meta name="robots" content="noindex, nofollow"> <!-- Empêche l'indexation des pages de connexion -->
    
    <title>Connexion - Technova</title>
    
    <!-- Feuilles de style -->
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
    
    <!-- Inclusion de l'en-tête du site -->
    <?php include 'components/header.php'; ?>
</head>
<body>
    <div class="background-wrapper"></div>
    
    <main class="main-content">
        <section class="formulaire-connexion">
            <div class="formulaire-container">
                <h1>Connexion à votre compte</h1>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
                
                <form class="form-group" method="POST" action="connexion.php" novalidate>
                    <div class="form-field">
                        <label for="email">Adresse email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="votre@email.com" 
                            value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                            required
                            autocomplete="username"
                            aria-describedby="email-help"
                        >
                        <small id="email-help" class="form-hint">Nous ne partagerons jamais votre email.</small>
                    </div>
                    
                    <div class="form-field">
                        <label for="password">Mot de passe</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Votre mot de passe" 
                            required
                            minlength="8"
                            autocomplete="current-password"
                            aria-describedby="password-help"
                        >
                        <small id="password-help" class="form-hint">
                            <a href="mot-de-passe-oublie.php">Mot de passe oublié ?</a>
                        </small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                        
                        <div class="form-footer">
                            <p>Pas encore de compte ? 
                                <a href="inscription.php" class="text-link">Créer un compte</a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!-- Inclusion du pied de page -->
    <?php require 'components/footer.php'; ?>
    
    <!-- Scripts JavaScript -->
    <script>
    // Validation côté client pour améliorer l'expérience utilisateur
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email');
                const password = document.getElementById('password');
                let isValid = true;
                
                // Validation de l'email
                if (!email.value || !email.validity.valid) {
                    email.classList.add('error');
                    isValid = false;
                } else {
                    email.classList.remove('error');
                }
                
                // Validation du mot de passe
                if (!password.value || password.value.length < 8) {
                    password.classList.add('error');
                    isValid = false;
                } else {
                    password.classList.remove('error');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    // Afficher un message d'erreur ou effectuer d'autres actions
                }
            });
        }
    });
    </script>
</body>
</html>