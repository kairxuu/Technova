<?php
/**
 * FICHIER : recherche.php
 * 
 * Ce fichier gère la recherche de produit dans la base de données.
 * Il affiche les résultats de la recherche sous forme de grille de produit.
 */

// Initialisation de la session pour gérer les variables de session
// La session permet de conserver des informations utilisateur entre les pages
session_start();

// Inclusion du fichier de connexion à la base de données
// Ce fichier contient les informations de connexion et initialise la variable $conn
require_once 'db/db.php';

/**
 * Récupération et sécurisation du terme de recherche depuis l'URL
 * - trim(): Supprime les espaces en début et fin de chaîne
 * - htmlspecialchars(): Convertit les caractères spéciaux en entités HTML
 *   pour se protéger contre les attaques XSS (Cross-Site Scripting)
 * - isset(): Vérifie si le paramètre 'q' existe dans l'URL
 */
$search = isset($_GET['q']) ? trim(htmlspecialchars($_GET['q'])) : '';

/**
 * Affiche la liste des produit dans une grille
 * 
 * @param mysqli_result $result Résultat de la requête SQL contenant les produit
 * @return void
 */
function displayProducts($result) {
    // Vérifie s'il y a des résultats à afficher
    if (mysqli_num_rows($result) > 0) {
        // Début du conteneur de la grille de produit
        echo '<div class="products-grid">';
        
        // Parcours de chaque produit dans le résultat de la requête
        while ($row = mysqli_fetch_assoc($result)) {
            // Récupération et sécurisation des données du produit avec vérification de l'existence des clés
            $id = isset($row['idpro']) ? intval($row['idpro']) : 0;  // ID du produit
            $nom = isset($row['prodnom']) ? htmlspecialchars($row['prodnom']) : 'Produit sans nom';  // Nom du produit (échappé pour la sécurité)
            
            // Formatage du prix avec 2 décimales, virgule comme séparateur décimal et espace comme séparateur de milliers
            $prix = isset($row['prodprix']) ? number_format(floatval($row['prodprix']), 2, ',', ' ') : '0,00';
            
            // Récupération de la marque si elle existe (sinon chaîne vide)
            $marque = isset($row['marnom']) && !empty($row['marnom']) ? htmlspecialchars($row['marnom']) : '';
            
            // Récupération de la description avec une valeur par défaut si elle n'existe pas
            $description = isset($row['proddesc']) ? htmlspecialchars($row['proddesc']) : 'Aucune description';
            ?>
            <!-- 
                CARTE PRODUIT
                Chaque produit est affiché dans une carte avec son image, son nom, sa description et son prix
            -->
            <article class="product-card" itemscope itemtype="https://schema.org/Product">
                <!-- Image du produit -->
                <img 
                    src="components/Images/<?= $id ?>.webp" 
                    alt="<?= $nom ?>" 
                    loading="lazy" 
                    onerror="this.onerror=null; this.src='components/Images/default-product.webp'"
                    itemprop="image">
                
                <div class="card-content">
                    <!-- Nom du produit -->
                    <h3 itemprop="name"><?= $nom ?></h3>
                    
                    <!-- Affichage conditionnel de la marque si elle existe -->
                    <?php if ($marque): ?>
                        <p class="product-brand" itemprop="brand" itemscope itemtype="https://schema.org/Brand">
                            <span itemprop="name"><?= $marque ?></span>
                        </p>
                    <?php endif; ?>
                    
                    <!-- Description du produit -->
                    <p class="product-description" itemprop="description">
                        <?= strlen($description) > 100 ? 
                            '<span class="truncated">' . substr($description, 0, 100) . '...</span>' : 
                            $description 
                        ?>
                    </p>
                    
                    <!-- Prix formaté -->
                    <div class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <meta itemprop="price" content="<?= str_replace(',', '.', $prix) ?>">
                        <meta itemprop="priceCurrency" content="EUR">
                        <span class="price-amount"><?= $prix ?> €</span>
                    </div>
                    
                    <!-- Bouton d'ajout au panier -->
                    <a 
                        href="ajouter_panier.php?id=<?= $id ?>" 
                        class="add-to-cart" 
                        aria-label="Ajouter <?= htmlspecialchars($nom) ?> au panier"
                        data-product-id="<?= $id ?>">
                        <i class="fas fa-cart-plus" aria-hidden="true"></i>
                        <span class="button-text">Ajouter au panier</span>
                    </a>
                </div>
            </article>
            <?php
        }
        // Fermeture de la grille de produit
        echo '</div>';
    } else {
        // Message affiché lorsqu'aucun résultat n'est trouvé
        echo '<div class="no-results">
                <i class="fas fa-search" aria-hidden="true"></i>
                <p>Aucun produit ne correspond à votre recherche.</p>
                <a href="produit.php" class="bouton">Voir tous nos produit</a>
              </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/recherche.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" 
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    <link rel="icon" href="/Technova/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
</head>
<body>
    <?php include 'components/header.php'; ?>

    <!-- 
        CONTENU PRINCIPAL
        Contient les résultats de la recherche ou un message d'erreur
    -->
    <main class="main-content" role="main">
        <?php if (!empty($search)): ?>
            <?php
            try {
                // Vérification de la connexion à la base de données
                if (!isset($conn) || !$conn) {
                    throw new Exception("Erreur de connexion à la base de données");
                }
                
                // Requête de recherche simple avec les bons noms de colonnes
                $sql = "SELECT p.ID_PRO as 'idpro', p.Nom as 'prodnom', p.Description as 'proddesc', 
                               p.Prix as 'prodprix', m.Nom as 'marnom' 
                        FROM produit p 
                        LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque 
                        WHERE p.Nom LIKE ? OR p.Description LIKE ?";
                $searchTerm = "%$search%";
                
                // Préparation de la requête
                $stmt = mysqli_prepare($conn, $sql);
                
                if (!$stmt) {
                    throw new Exception("Erreur lors de la préparation de la requête : " . mysqli_error($conn));
                }
                
                // Liaison des paramètres
                mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
                
                // Exécution de la requête
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                // Affichage des résultats
                if (mysqli_num_rows($result) > 0) {
                    displayProducts($result);
                } else {
                    echo '<div class="no-results">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <p>Aucun produit ne correspond à votre recherche.</p>
                            <a href="produit.php" class="bouton">Voir tous nos produit</a>
                          </div>';
                }
                
                // Fermeture du statement
                mysqli_stmt_close($stmt);
                
            } catch (Exception $e) {
                echo '<div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Erreur : ' . htmlspecialchars($e->getMessage()) . '</p>
                      </div>';
            }
            ?>
        <?php else: ?>
            <div class="no-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <p>Veuillez entrer un terme de recherche dans la barre ci-dessus.</p>
                <a href="produit.php" class="bouton">Voir tous nos produit</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Inclusion du pied de page -->
    <?php 
    if (!headers_sent()) {
        include 'components/footer.php'; 
    }
    ?>
    <!-- Scripts JavaScript -->
    <script>
        // Script pour améliorer l'expérience utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol des cartes produit
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                // Animation au survol
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                });
                
                // Gestion du clic sur la carte pour aller à la page du produit
                card.addEventListener('click', function(e) {
                    // Empêche la navigation si on clique sur un lien ou un bouton
                    if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && e.target.closest('a, button') === null) {
                        const productId = this.querySelector('a[data-product-id]')?.getAttribute('data-product-id');
                        if (productId) {
                            window.location.href = `produit.php?id=${productId}`;
                        }
                    }
                });
            });
            
            // Gestion de l'ajout au panier avec feedback visuel
            const addToCartButtons = document.querySelectorAll('.add-to-cart');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    
                    // Animation de feedback
                    this.classList.add('adding');
                    this.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
                    
                    // Réinitialisation après l'animation
                    setTimeout(() => {
                        this.classList.remove('adding');
                        this.innerHTML = '<i class="fas fa-cart-plus"></i> Ajouter au panier';
                    }, 2000);
                    
                    // Ici, vous pourriez ajouter un appel AJAX pour ajouter le produit au panier
                    // sans recharger la page
                    window.location.href = this.href; // Pour l'instant, on recharge la page
                });
            });
        });
    </script>
</body>
</html>

<?php
if (isset($conn)) mysqli_close($conn);
?>
