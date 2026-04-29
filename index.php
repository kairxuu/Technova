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
                    mysqli_data_seek($res, 0); // Reset du pointeur
                    // Affichage des produits
                    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
                    {
                        // Données du produit
                        $lenom = isset($row["prodnom"]) ? htmlspecialchars($row["prodnom"]) : 'Produit sans nom';
                        $id = isset($row["idpro"]) ? intval($row["idpro"]) : 0;
                        $leprix = isset($row["prodprix"]) ? number_format(floatval($row["prodprix"]), 2, ',', ' ') : '0,00';
                        $marque = !empty($row["marnom"]) ? 'Marque : ' . htmlspecialchars($row["marnom"]) : '';
                        $description = isset($row["proddesc"]) ? htmlspecialchars($row["proddesc"]) : 'Aucune description disponible';
                        $image = !empty($row["image"]) ? htmlspecialchars($row["image"]) : $id . '.webp';
                    ?>
                    
                    <!-- Carte produit -->
                    <div class="product-card">

                        <img src="components/images_pc/<?=$image?>" alt="<?=$lenom?>">

                        <div class="card-content">

                            <h3><?php echo $lenom; ?></h3>

                            <?php if (!empty($marque)): ?>
                                <p class="marque"><?php echo $marque; ?></p>
                            <?php endif; ?>

                            <p class="description"><?php echo $description; ?></p>

                            <p class="prix"><strong>Prix : <?php echo $leprix; ?> €</strong></p>

                            <form action="panier.php" method="get" class="add-to-cart-form">
                                <input type="hidden" name="action" value="ajouter">
                                <input type="hidden" name="id" value="<?php echo $id; ?>">
                                <button type="submit" class="add-to-cart" title="Ajouter au panier">
                                    <svg class="icon" viewBox="0 0 24 24" width="16" height="16">
                                        <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                                    </svg>
                                    Ajouter au panier
                                </button>
                            </form>
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