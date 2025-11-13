<?php 
/**
 * Gestion de la session
 * On vérifie d'abord si une session n'est pas déjà active avant d'en démarrer une nouvelle
 * Cela évite les erreurs de "session already started"
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Métadonnées de la page -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TN Commerce</title>
    <!-- Inclusion de la feuille de style de la navbar -->
    <link rel="stylesheet" href="/Technova/CSS/navbar.css">
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/components.css">
</head>

<body>
    <!-- Barre de navigation principale -->
    <nav class="navbar">
        <!-- Logo cliquable qui renvoie vers la page d'accueil -->
        <div class="logo">
            <a href="/TEST/MENU/index.php" class="logo-link"><h1>TN</h1></a>
        </div>
        
        <!-- Barre de recherche (pour une fonctionnalité future) -->
        <div class="search-bar">
            <input type="text" placeholder="Rechercher...">
            <a href="#" class="search-button"><span>🔍</span></a>
        </div>
        
        <!-- Menu hamburger pour la version mobile -->
        <input type="checkbox" id="toggle" class="toggle-checkbox">
        <label for="toggle" class="hamburger">☰</label>
        
        <!-- Menu principal de navigation -->
        <div class="menu">
            <!-- Liens de navigation standards -->
            <a class="menu-item" href="/Technova/index.php">Accueil</a>
            <a class="menu-item" href="/Technova/produits.php">Produits</a>
            <a class="menu-item" href="/Technova/about.php">À propos</a>
            
            <?php 
            // Affichage conditionnel en fonction de l'état de connexion
            if (isset($_SESSION['user_id'])) { 
                // Si l'utilisateur est connecté, on affiche le bouton de déconnexion
                ?>
                <div class="menu-session">
                    <a href="/Technova/deconnexion.php" class="menu-item">Déconnexion</a>
                </div>
                <?php 
            } else { 
                // Si l'utilisateur n'est pas connecté, on affiche le bouton de connexion
                ?>
                <a class="menu-item" href="/Technova/connexion.php">Connexion</a>
                <?php 
            } 
            ?>
            
            <!-- Lien vers le panier -->
            <a class="menu-item" href="/Technova/panier.php">Panier</a>
        </div>
    </nav>
</body>
