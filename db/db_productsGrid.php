<div class="products-grid">
                <?php
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
                        <!-- Image du produit -->
                        <img src="components/Images/<?=$id?>.webp" alt="<?=$lenom?>">

                        <!-- Affichage du nom du produit -->
                        <h3><?php echo $lenom; ?></h3>
                        
                        <!-- Affichage de la marque du produit (si disponible) -->
                        <?php if (!empty($marque)): ?>
                            <p class="product-brand"><?php echo $marque; ?></p>
                        <?php endif; ?>
                        
                        <!-- Description du produit avec limitation de caractères -->
                        <p class="product-description">
                            <?php 
                            // Affichage des 100 premiers caractères de la description
                            echo strlen($description) > 100 ? 
                                substr($description, 0, 100) . '...' : 
                                $description; 
                            ?>
                        </p>
                        
                        <!-- Prix du produit -->
                        <p class="product-price">Prix: <?php echo $leprix; ?> €</p>
                        
                        <!-- Bouton d'ajout au panier -->
                        <button 
                            class="add-to-cart" 
                            onclick="window.location.href='ajouter_panier.php?id=<?php echo $id; ?>'"
                            title="Ajouter au panier">
                            <i class="fas fa-cart-plus"></i> Ajouter au panier
                        </button>
                    </div>
                    
                <?php
                    }
                ?>
                
                
            </div>