<?php
session_start();
require_once "db.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Accueil</title>
    <link href="CSS/general.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="top-nav">
            <div class="nav-container">
                <a href="index.php" class="nav-brand">Technova</a>
                <div class="nav-menu">
                    <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Accueil</a>
                    <a href="produits.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'produits.php' ? 'active' : ''; ?>">Produits</a>
                    <a href="contact.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a>
                    <a href="panier.php" class="nav-item 🛒" style="font-size: 1.2rem;">🛒</a>
                    <?php if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0): ?>
                        <span class="nav-item user-info">👤 <?=$_SESSION["username"]?></span>
                        <a href="logout.php" class="nav-item logout-btn">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="nav-item login-btn <?php echo basename($_SERVER['PHP_SELF']) == 'connexion.php' ? 'active' : ''; ?>">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Bannière principale -->
    <section class="hero">
        <div class="hero-content" style="margin-top: 5%;">
            <h1>Technova</h1>
            <h2>Découvrez l'innovation technologique</h2>
            <p>Explorez notre collection de produits high-tech dernier cri</p>
            <div class="hero-buttons" style="margin-top: 5%;">
                <a href="produits.php" class="btn-primary">Voir les produits</a>
                <a href="panier.php" class="btn-secondary">Voir le panier</a>
            </div>
        </div>
    </section>

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="container">
            <h2 style="text-align: center; margin: 40px 0; font-size: 1.8rem; color: #fff;">Nos produits phares</h2>
            <div class="products-grid">
                    <?php
                    // Récupérer 3 produits au hasard
                    $sql = "SELECT p.ID_PRO, p.Nom, p.Prix, m.Nom as marque
                            FROM produit p
                            LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque
                            ORDER BY RAND()
                            LIMIT 3";

                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                            $id = $row['ID_PRO'];
                            $nom = $row['Nom'];
                            $prix = $row['Prix'];
                            $marque = $row['marque'] ?? 'Non spécifiée';
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/<?=$id?>.webp" alt="<?=$nom?>" onerror="this.src='assets/no-image.png'">
                        </div>
                        <div class="product-info">
                            <h3><?=$nom?></h3>
                            <p class="brand"><?=$marque?></p>
                            <p class="price"><?=$prix?> €</p>
                            <a href="produits.php" class="btn-primary">Voir détails</a>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                    ?>
                    <p>Aucun produit disponible pour le moment.</p>
                    <?php
                    }
                    ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-section">
            <h3>À propos</h3>
            <p>Technova - Technologies innovantes</p>
            <p>&copy; 2024 Technova</p>
        </div>
    </footer>
</body>
</html>
