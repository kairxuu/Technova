<?php
session_start();
require_once "db.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Tous les produits</title>
    <link href="CSS/general.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="top-nav">
            <div class="nav-container">
                <a href="index.php" class="nav-brand">Technova</a>
                <div class="nav-menu">
                    <a href="index.php" class="nav-item">Accueil</a>
                    <a href="produits.php" class="nav-item active">Produits</a>
                    <a href="contact.php" class="nav-item">Contact</a>
                    <a href="panier.php" class="nav-item" style="font-size: 1.2rem;">🛒</a>
                    <?php if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0): ?>
                        <span class="nav-item user-info">👤 <?=$_SESSION["username"]?></span>
                        <a href="logout.php" class="nav-item logout-btn">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="nav-item login-btn">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <h1 class="page-title">Tous nos produits</h1>

        <div class="products-grid">
            <?php
            $sql = "SELECT p.ID_PRO, p.Nom, p.Description, p.Prix, m.Nom as marque
                    FROM produit p
                    LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque
                    ORDER BY p.Nom";

            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    $id = $row['ID_PRO'];
                    $nom = $row['Nom'];
                    $description = substr($row['Description'], 0, 100) . "...";
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
                    <p class="description"><?=$description?></p>
                    <p class="price"><?=$prix?> €</p>
                    <div class="product-actions">
                        <a href="panier.php?idpro=<?=$id?>" class="btn-primary">Ajouter au panier</a>
                    </div>
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
