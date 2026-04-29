<?php 
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$cartCount = isset($_SESSION['panier']) ? count($_SESSION['panier']) : 0;
$sql = '';

if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $sql = "SELECT Id_Client FROM client WHERE ID_Client = " . $user_id;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Technova' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/navbar.css">
    <link rel="stylesheet" href="/Technova/CSS/components.css">
    <link rel="stylesheet" href="/Technova/CSS/footer.css">
    <style>
        .icon { width: 1em; height: 1em; margin-right: 0.5em; vertical-align: -0.15em; fill: currentColor; }
    </style>

    <?php
    // CSS et JS supplémentaires définis par la page
    if (!empty($extraCSS)) foreach ($extraCSS as $css) echo '<link rel="stylesheet" href="' . htmlspecialchars($css) . '">' . "\n";
    if (!empty($extraJS))  foreach ($extraJS  as $js)  echo '<script src="' . htmlspecialchars($js) . '" defer></script>' . "\n";
    ?>
</head>

<body>

    <?php if (!empty($_SESSION['toast'])): ?>
    <div id="toast-notif" style="
        position: fixed;
        top: 80px;
        right: 24px;
        z-index: 9999;
        background: #18181b;
        color: #f4f4f5;
        border-radius: 10px;
        padding: 13px 20px;
        font-family: var(--font, sans-serif);
        font-size: 0.9rem;
        font-weight: 500;
        box-shadow: 0 4px 20px rgba(0,0,0,0.35);
        min-width: 220px;
        max-width: 340px;
        animation: toastIn 0.25s ease;
    "><?= htmlspecialchars($_SESSION['toast']) ?></div>
    <style>
        @keyframes toastIn  { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes toastOut { from { opacity:1; transform:translateY(0); }    to { opacity:0; transform:translateY(-10px); } }
    </style>
    <script>
        setTimeout(function() {
            var t = document.getElementById('toast-notif');
            if (t) { t.style.animation = 'toastOut 0.3s ease forwards'; setTimeout(function(){ t.remove(); }, 300); }
        }, 3000);
    </script>
    <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <nav class="navbar">
        <a href="index.php" class="logo"><span>Technova</span></a>

        <!-- Navigation -->
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
            
            <!-- Profil -->
            <li class="profile-dropdown-wrapper">
                <?php if (isset($_SESSION['user_id'])): ?>
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
