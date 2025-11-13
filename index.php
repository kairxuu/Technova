<?php 
require_once 'db/db.php';
include 'db/db_implement.php';
include 'components/header.php';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link rel="stylesheet" href="CSS/general.css">
    <link rel="stylesheet" href="CSS/components.css">

</head>


<body>    
    <div class="main-content">
    <div class="background-wrapper"></div>
    
    <section class="hero-banner">
        <div class="hero-banner-content">
            <h1>TECHNOVA</h1>
            <h3>La vente pour les compétiteurs.</h3>
            <div class="buttons-hero">
                <button class="primary" onclick="window.location.href='produits.php'">Voir tous les produits</button>
                <button class="primary" onclick="window.location.href='panier.php'">Voir le panier</button>
            </div>
        </div>
    </section>

    <section class="preview-prod">
        <div class="container">
            <h2>Nos produits populaires</h2>

            <div class="products-grid">
                <?php
                    // Réinitialiser le pointeur du résultat
                    mysqli_data_seek($res, 0);
                    // Boucle pour afficher chaque produit
                    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
                    {
                        // Récupération des informations du produit
                        $lenom = isset($row["prodnom"]) ? htmlspecialchars($row["prodnom"]) : 'Produit sans nom';
                        $id = isset($row["idpro"]) ? intval($row["idpro"]) : 0;
                        $leprix = isset($row["prodprix"]) ? number_format(floatval($row["prodprix"]), 2, ',', ' ') : '0,00';
                        $marque = !empty($row["marnom"]) ? 'Marque : ' . htmlspecialchars($row["marnom"]) : '';
                        $description = isset($row["proddesc"]) ? htmlspecialchars($row["proddesc"]) : 'Aucune description disponible';
                    ?>
                    
                    <!-- Carte de produit individuelle -->
                    <div class="product-card">
                        <img src="components/Images/<?=$id?>.webp" alt="<?=$lenom?>">
                        <div class="card-content">
                            <h3><?php echo $lenom; ?></h3>
                            <?php if (!empty($marque)): ?>
                                <p class="marque"><?php echo $marque; ?></p>
                            <?php endif; ?>
                            <p class="description"><?php echo $description; ?></p>
                            <p class="prix"><strong>Prix : <?php echo $leprix; ?> €</strong></p>
                            <button 
                                class="primary" 
                                onclick="window.location.href='ajouter_panier.php?id=<?php echo $id; ?>'"
                                title="Ajouter au panier">
                                <i class="fas fa-cart-plus"></i> Ajouter au panier
                            </button>
                        </div>
                    </div>
                    
                <?php
                    }
                ?>
            </div>
        </div>
    </section>
    </div> <!-- Fin de .main-content -->
    <?php require 'components/footer.php'; ?>
</body>
</html>