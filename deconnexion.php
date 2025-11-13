<?php
/**
 * Fichier : deconnexion.php
 * Description : Gère la déconnexion des utilisateurs en nettoyant la session
 */

// Vérification si une session est déjà active avant d'en démarrer une nouvelle
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Suppression de toutes les variables de session
// Cette ligne vide complètement le tableau $_SESSION
$_SESSION = array();

// 2. Suppression du cookie de session côté client
// On vérifie d'abord si les cookies sont utilisés pour la session
if (ini_get("session.use_cookies")) {
    // Récupération des paramètres du cookie de session
    $params = session_get_cookie_params();
    
    // Création d'un cookie avec une date d'expiration dans le passé pour le supprimer
    setcookie(
        session_name(),  // Nom du cookie de session (généralement 'PHPSESSID')
        '',              // Valeur vide
        time() - 42000,  // Date d'expiration dans le passé
        $params["path"],     // Chemin du cookie
        $params["domain"],   // Domaine du cookie
        $params["secure"],   // Sécurisé (HTTPS)
        $params["httponly"]  // Accessible uniquement en HTTP (pas en JavaScript)
    );
}

// 3. Destruction de la session côté serveur
// Cette fonction supprime toutes les données enregistrées dans la session
session_destroy();

// 4. Redirection vers la page d'accueil
// Utilisation d'un code de statut 302 (Found) pour la redirection
header("Location: index.php");

// 5. Arrêt de l'exécution du script
// Important pour s'assurer qu'aucun autre code ne s'exécute après la redirection
exit();

// Note : Après une déconnexion, l'utilisateur devra se reconnecter
// pour accéder aux pages protégées
?>
