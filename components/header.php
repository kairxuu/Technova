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
            
            <!-- Bouton Profil avec Dropdown -->
            <li class="profile-dropdown-wrapper">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Utilisateur connecté : affiche son nom -->
                    <button class="profile-btn profile-btn--logged" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <svg class="profile-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown" role="menu">
                        <div class="profile-dropdown__header">
                            <span class="profile-dropdown__name"><?= htmlspecialchars($_SESSION['username']) ?></span>
                            <span class="profile-dropdown__label">Mon compte</span>
                        </div>
                        <div class="profile-dropdown__divider"></div>
                        <a href="deconnexion.php" class="profile-dropdown__item profile-dropdown__item--danger" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                            </svg>
                            Déconnexion
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Utilisateur non connecté : icône profil générique -->
                    <button class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
                        </svg>
                        <span>Mon compte</span>
                        <svg class="profile-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7 10l5 5 5-5z"/>
                        </svg>
                    </button>
                    <div class="profile-dropdown" id="profileDropdown" role="menu">
                        <a href="connexion.php" class="profile-dropdown__item" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                            </svg>
                            Se connecter
                        </a>
                        <a href="inscription.php" class="profile-dropdown__item" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                            S'inscrire
                        </a>
                    </div>
                <?php endif; ?>
            </li>
            
            <!-- Panier -->
            <li><a href="panier.php" class="menu-item">
                <svg class="icon" viewBox="0 0 24 24" width="24" height="24">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                <span>Panier</span>
            </a></li>
        </ul>
    </nav>

    <script>
        (function () {
            var btn = document.getElementById('profileBtn');
            var dropdown = document.getElementById('profileDropdown');
            if (!btn || !dropdown) return;

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                var isOpen = dropdown.classList.toggle('profile-dropdown--open');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                btn.classList.toggle('profile-btn--active', isOpen);
            });

            document.addEventListener('click', function () {
                dropdown.classList.remove('profile-dropdown--open');
                btn.setAttribute('aria-expanded', 'false');
                btn.classList.remove('profile-btn--active');
            });

            dropdown.addEventListener('click', function (e) {
                e.stopPropagation();
            });
        })();
    </script>

    <main class="main-content">
