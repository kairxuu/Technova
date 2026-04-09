<?php 
// Vérification si une session n'est pas déjà active avant d'en démarrer une nouvelle
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Récupération du nombre d'articles dans le panier
$cartCount = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;

// Initialisation de la requête SQL
$sql = '';

// Vérification si l'utilisateur est connecté avant de construire la requête
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']); // Sécurisation de l'ID utilisateur
    $sql = "SELECT Id_Client FROM client WHERE ID_Client = " . $user_id;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova</title>
    
    <!-- Google Fonts : Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Feuilles de style -->
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/navbar.css">
    <link rel="stylesheet" href="/Technova/CSS/components.css">
    
    <!-- Icônes SVG en ligne pour les éléments de menu -->
    <style>
        .icon {
            width: 1em;
            height: 1em;
            margin-right: 0.5em;
            vertical-align: -0.15em;
            fill: currentColor;
        }
    </style>
</head>

<body>
    <!-- Barre de navigation -->
    <nav class="navbar">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <span>Technova</span>
        </a>
        
        <!-- Barre de recherche -->
        <form action="recherche.php" method="GET" class="search-bar" role="search">
            <input 
                type="text" 
                name="q" 
                placeholder="Rechercher un produit..." 
                aria-label="Rechercher un produit"
                required>
            <button type="submit" class="search-button" aria-label="Lancer la recherche">
                <svg class="icon" viewBox="0 0 24 24" width="18" height="18">
                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                </svg>
            </button>
        </form>
        
        <!-- Menu de navigation -->
        <ul class="menu">
            <li><a href="index.php" class="menu-item" aria-current="page">
                <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                </svg>
                <span>Accueil</span>
            </a></li>
            
            <li><a href="produits.php" class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/>
                </svg>
                <span>Produits</span>
            </a></li>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="nav-item user-info" title="Connecté">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <?=htmlspecialchars($_SESSION["username"])?></span>   
                <!-- Lien de déconnexion visible uniquement quand connecté -->
                <li><a href="deconnexion.php" class="menu-item">
                    
                    <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    <span>Déconnexion</span>
                </a></li>
            <?php else: ?>
                <!-- Liens visibles uniquement quand déconnecté -->
                <li><a href="connexion.php" class="menu-item">
                    <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                    </svg>
                    <span>Connexion</span>
                </a></li>
                
                <li><a href="inscription.php" class="menu-item">
                    <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <span>Inscription</span>
                </a></li>
            <?php endif; ?>
            
            <!-- Panier -->
            <li><a href="panier.php" class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span>Panier</span>
            </a></li>
        </ul>
    </nav>
    

    <main class="main-content">
