<div class="products-grid">
                <?php
                    require_once __DIR__ . '/db_implement.php'; // Garantit que $res est disponible
                    while($row = mysqli_fetch_array($res, MYSQLI_ASSOC))
                    {
                        // Données du produit
                        $lenom     = isset($row["prodnom"])  ? htmlspecialchars($row["prodnom"])  : 'Produit sans nom';
                        $id        = isset($row["idpro"])    ? intval($row["idpro"])              : 0;
                        $prix_brut = isset($row["prodprix"]) ? floatval($row["prodprix"])         : 0;            // prix brut pour JS
                        $leprix    = isset($row["prodprix"]) ? number_format(floatval($row["prodprix"]), 2, ',', ' ') : '0,00'; // prix affiché
                        $marque      = !empty($row["marnom"])  ? 'Marque : ' . htmlspecialchars($row["marnom"]) : '';
                        $description = isset($row["proddesc"]) ? htmlspecialchars($row["proddesc"]) : 'Aucune description disponible';
                        $image       = !empty($row["image"])   ? htmlspecialchars($row["image"])   : $id . '.webp';
                    ?>
                    
                    <!-- data-price avec point décimal pour JS -->
                    <div class="product-card" data-price="<?php echo sprintf('%.2f', $prix_brut); ?>">
                        <img src="components/images_pc/<?=$image?>" alt="<?=$lenom?>">
                        <h3><?php echo $lenom; ?></h3>
                        <?php if (!empty($marque)): ?>
                            <p class="product-brand"><?php echo $marque; ?></p>
                        <?php endif; ?>

                        <p class="product-description">
                            <?php // Tronque la description à 100 caractères
                            echo strlen($description) > 100 ?
                                substr($description, 0, 100) . '...' :
                                $description;
                            ?>
                        </p>
                        
                        <!-- Prix -->
                        <div class="product-price" data-produit-id="<?php echo $id; ?>">
                            <?php echo $leprix; ?> €
                        </div>
                        
                        <!-- Bouton panier -->
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