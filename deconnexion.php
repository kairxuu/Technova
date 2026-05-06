<?php
// --- DÉCONNEXION ---
// Détruit la session de l'utilisateur et le redirige vers l'accueil.

// Démarre la session si elle n'est pas encore active (nécessaire pour pouvoir la détruire)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vide toutes les variables de session
$_SESSION = [];

// Supprime le cookie de session côté navigateur (en lui donnant une date d'expiration passée)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), // Nom du cookie (ex: PHPSESSID)
        '',             // Valeur vide
        time() - 42000, // Date dans le passé → le navigateur supprime le cookie
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Détruit la session côté serveur
session_destroy();

// Redirige vers la page d'accueil
header("Location: index.php");
exit();
?>
