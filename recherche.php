<?php
// Recherche de produits par nom ou description.

session_start();
require_once 'db/db.php';

// Terme de recherche sécurisé
$search = isset($_GET['q']) ? trim(htmlspecialchars($_GET['q'])) : '';

// Affiche les produits sous forme de grille
function displayProducts($result) {
    if (mysqli_num_rows($result) > 0) {
        echo '<div class="products-grid">';
        while ($row = mysqli_fetch_assoc($result)) {
            $id          = isset($row['idpro'])    ? intval($row['idpro'])                     : 0;
            $nom         = isset($row['prodnom'])  ? htmlspecialchars($row['prodnom'])          : 'Produit sans nom';
            $prix        = isset($row['prodprix']) ? number_format(floatval($row['prodprix']), 2, ',', ' ') : '0,00';
            $marque      = !empty($row['marnom'])  ? htmlspecialchars($row['marnom'])           : '';
            $description = isset($row['proddesc']) ? htmlspecialchars($row['proddesc'])         : 'Aucune description';
            $image       = !empty($row['image'])   ? htmlspecialchars($row['image'])            : $id . '.webp';
            ?>
            <!-- Carte produit -->
            <article class="product-card" itemscope itemtype="https://schema.org/Product">
                <img src="components/images_pc/<?= $image ?>" alt="<?= $nom ?>"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='components/images_pc/default-product.webp'"
                     itemprop="image">

                <div class="card-content">
                    <h3 itemprop="name"><?= $nom ?></h3>

                    <?php if ($marque): ?>
                        <p class="product-brand" itemprop="brand" itemscope itemtype="https://schema.org/Brand">
                            <span itemprop="name"><?= $marque ?></span>
                        </p>
                    <?php endif; ?>

                    <p class="product-description" itemprop="description">
                        <?= strlen($description) > 100 ?
                            '<span class="truncated">' . substr($description, 0, 100) . '...</span>' :
                            $description ?>
                    </p>

                    <div class="product-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <meta itemprop="price" content="<?= str_replace(',', '.', $prix) ?>">
                        <meta itemprop="priceCurrency" content="EUR">
                        <span class="price-amount"><?= $prix ?> €</span>
                    </div>

                    <a href="ajouter_panier.php?id=<?= $id ?>"
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
        echo '</div>';
    } else {
        echo '<div class="no-results">
                <i class="fas fa-search" aria-hidden="true"></i>
                <p>Aucun produit ne correspond à votre recherche.</p>
                <a href="produit.php" class="bouton">Voir tous nos produits</a>
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
          crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" href="/Technova/favicon.ico" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
</head>
<body>
    <?php include 'components/header.php'; ?>

    <main class="main-content" role="main">
        <?php if (!empty($search)): ?>
            <?php
            try {
                if (!isset($conn) || !$conn) throw new Exception("Erreur de connexion à la base de données");

                $sql = "SELECT p.ID_PRO as 'idpro', p.Nom as 'prodnom', p.Description as 'proddesc',
                               p.Prix as 'prodprix', m.Nom as 'marnom', p.image as 'image'
                        FROM produit p
                        LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque
                        WHERE p.Nom LIKE ? OR p.Description LIKE ?";

                $searchTerm = "%$search%";
                $stmt = mysqli_prepare($conn, $sql);
                if (!$stmt) throw new Exception("Erreur de préparation : " . mysqli_error($conn));

                mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) displayProducts($result);
                else echo '<div class="no-results">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                <p>Aucun produit ne correspond à votre recherche.</p>
                                <a href="produit.php" class="bouton">Voir tous nos produits</a>
                           </div>';

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
                <a href="produit.php" class="bouton">Voir tous nos produits</a>
            </div>
        <?php endif; ?>
    </main>

    <?php if (!headers_sent()) include 'components/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hover sur les cartes
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
            });
            // Clic sur la carte → page produit
            card.addEventListener('click', function(e) {
                if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON' && e.target.closest('a, button') === null) {
                    const productId = this.querySelector('a[data-product-id]')?.getAttribute('data-product-id');
                    if (productId) window.location.href = `produit.php?id=${productId}`;
                }
            });
        });

        // Feedback visuel ajout panier
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.add('adding');
                this.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
                setTimeout(() => {
                    this.classList.remove('adding');
                    this.innerHTML = '<i class="fas fa-cart-plus"></i> Ajouter au panier';
                }, 2000);
                window.location.href = this.href;
            });
        });
    });
    </script>
</body>
</html>

<?php if (isset($conn)) mysqli_close($conn); ?>
