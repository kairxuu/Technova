<?php
/**
 * Fichier : ajouter_panier.php
 * Description : Gère l'ajout de produits au panier d'achat
 * Sécurisé pour n'accepter que les utilisateurs connectés
 */

// 1. Gestion de la session
// Vérification si une session est déjà active avant d'en démarrer une nouvelle
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2. Vérification de l'authentification
// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    // On enregistre l'URL actuelle pour y rediriger l'utilisateur après connexion
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: connexion.php');
    exit();
}

// 3. Validation des paramètres d'entrée
// Vérification de la présence et de la validité de l'ID du produit
if (!isset($_GET['id_produit']) || !is_numeric($_GET['id_produit'])) {
    // Redirection avec un message d'erreur si l'ID du produit est invalide
    header('Location: produits.php?erreur=produit_invalide');
    exit();
}

// Nettoyage des données d'entrée
// Conversion de l'ID en nombre entier pour éviter les injections
$id_produit = intval($_GET['id_produit']);

// Récupération de la quantité (par défaut à 1 si non spécifiée)
// La fonction max(1, ...) garantit que la quantité est au moins de 1
$quantite = isset($_GET['quantite']) ? max(1, intval($_GET['quantite'])) : 1;

// 4. Gestion du panier dans la session
// Initialisation du panier s'il n'existe pas encore
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// 5. Ajout du produit au panier
// Si le produit est déjà dans le panier, on incrémente sa quantité
// Sinon, on l'ajoute avec la quantité spécifiée
if (isset($_SESSION['panier'][$id_produit])) {
    $_SESSION['panier'][$id_produit] += $quantite;
} else {
    $_SESSION['panier'][$id_produit] = $quantite;
}

// 6. Redirection
// On essaie de rediriger vers la page précédente (HTTP_REFERER)
// Si ce n'est pas disponible, on redirige vers la page du panier
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'panier.php';

// Envoyer un en-tête de redirection
header("Location: $redirect");

// Arrêt de l'exécution du script après la redirection
exit();

// Note : Ce script ne contient pas de sortie HTML car il ne fait que traiter
// les données et rediriger l'utilisateur
?>
