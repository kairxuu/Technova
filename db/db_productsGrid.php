<div class="products-grid">
                <?php
                    // Boucle pour afficher chaque produit
                    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
                    {
                        // Récupération des informations du produit
                        $lenom = isset($row["prodnom"]) ? htmlspecialchars($row["prodnom"]) : 'Produit sans nom';
                        $id = isset($row["idpro"]) ? intval($row["idpro"]) : 0;
                        // On garde le prix brut (nombre décimal) pour le filtre JavaScript
                        $prix_brut = isset($row["prodprix"]) ? floatval($row["prodprix"]) : 0;
                        // On formate le prix pour l'affichage (ex: 1 299,99 €)
                        $leprix = isset($row["prodprix"]) ? number_format(floatval($row["prodprix"]), 2, ',', ' ') : '0,00';
                        $marque = !empty($row["marnom"]) ? 'Marque : ' . htmlspecialchars($row["marnom"]) : '';
                        $description = isset($row["proddesc"]) ? htmlspecialchars($row["proddesc"]) : 'Aucune description disponible';
                        $image = !empty($row["image"]) ? htmlspecialchars($row["image"]) : $id . '.webp';
                    ?>
                    
                    <!-- data-price toujours avec un POINT (ex: 1299.99) pour que JS puisse le lire -->
                    <div class="product-card" data-price="<?php echo sprintf('%.2f', $prix_brut); ?>">
                        <!-- Image du produit -->
                        <img src="components/images_pc/<?=$image?>" alt="<?=$lenom?>">

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
                        <div class="product-price" 
                             data-produit-id="<?php echo $id; ?>">
                            <?php echo $leprix; ?> €
                        </div>
                        
                        <!-- Bouton d'ajout au panier -->
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
                    
                <?php
                    }
                ?>
                
                
            </div>