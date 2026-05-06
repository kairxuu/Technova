<?php
// --- PANIER ---
// Gère l'ajout, la réduction et la suppression d'articles dans le panier (stocké en session).
// Affiche ensuite le contenu du panier avec les totaux.

// Démarre la session si elle n'est pas encore active
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once 'db/db.php';        // Connexion à la base de données ($conn)
require_once 'db/db_implement.php'; // Requête produits ($res)

// CSS et titre spécifiques à cette page (injectés par header.php)
$extraCSS  = ['/Technova/CSS/panier.css'];
$pageTitle = "Mon panier — Technova";

// Initialise le panier comme tableau vide s'il n'existe pas encore
if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];

// Traitement des actions (ajout, réduction, suppression) déclenchées via l'URL (?action=...)
if (isset($_GET['action'])) {

    // Seuls les utilisateurs connectés peuvent modifier le panier
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = 'panier.php'; // Mémorise la page pour y revenir après connexion
        header('Location: connexion.php');
        exit();
    }

    // Récupère l'ID du produit depuis l'URL et vérifie que c'est bien un entier
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false) {
        $_SESSION['erreur'] = 'ID de produit invalide.';
    } else {
        switch ($_GET['action']) {
            case 'ajouter':
                // Incrémente la quantité (ou l'initialise à 1 si absent)
                $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;
                $_SESSION['toast'] = '✅ Produit ajouté au panier !';
                break;

            case 'supprimer':
                // Réduit la quantité d'1 ; supprime le produit si quantité = 0
                if (isset($_SESSION['panier'][$id])) {
                    if ($_SESSION['panier'][$id] > 1) { $_SESSION['panier'][$id]--; $_SESSION['toast'] = 'Quantité réduite.'; }
                    else                               { unset($_SESSION['panier'][$id]); $_SESSION['toast'] = 'Produit retiré du panier.'; }
                }
                break;

            case 'vider':
                // Supprime complètement le produit du panier
                if (isset($_SESSION['panier'][$id])) {
                    unset($_SESSION['panier'][$id]);
                    $_SESSION['toast'] = 'Produit retiré du panier.';
                }
                break;
        }
    }

    // Redirige vers la page précédente pour éviter la re-soumission en cas de rechargement (F5)
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'panier.php'));
    exit();
}

// Charge l'en-tête du site (navbar) uniquement si aucune action n'a redirigé
require_once 'components/header.php';
?>

<main class="main-content">
    <section class="panier-section">

        <!-- Message de confirmation (ex: "Produit ajouté") -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Message d'erreur (ex: "ID invalide") -->
        <?php if (!empty($_SESSION['erreur'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erreur']) ?></div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <div class="panier-content">
            <?php if (empty($_SESSION['panier'])): ?>
                <!-- Panier vide -->
                <div class="panier-vide">
                    <p>Votre panier est vide.</p>
                    <a href="produits.php" class="btn-continuer">Découvrir nos produits</a>
                </div>

            <?php else:
                require_once 'db/db.php';

                // Construit les placeholders (?,?,?) pour la requête IN
                $ids          = array_keys($_SESSION['panier']);
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';

                // Récupère les infos des produits présents dans le panier
                $query = "SELECT p.ID_PRO as id, p.Nom as nom, p.Prix as prix, p.image as image,
                                 m.Nom as marque
                          FROM produit p
                          LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque
                          WHERE p.ID_PRO IN ($placeholders)";

                $stmt = $conn->prepare($query);
                $stmt->execute($ids); // Liaison sécurisée des IDs
                $result = $stmt->get_result();

                $total       = 0;
                $panierItems = [];

                // Calcule les quantités et sous-totaux pour chaque produit
                while ($row = $result->fetch_assoc()) {
                    $idProduit            = $row['id'];
                    $quantite             = isset($_SESSION['panier'][$idProduit]) ? (int)$_SESSION['panier'][$idProduit] : 1;
                    $prixUnitaire         = (float)$row['prix'];
                    $row['quantite']      = $quantite;
                    $row['prix_unitaire'] = $prixUnitaire;
                    $row['prix_total']    = $prixUnitaire * $quantite;
                    $panierItems[]        = $row;
                }

                // Calcule le total général du panier
                foreach ($panierItems as &$item) $total += $item['prix_total'];
            ?>

            <!-- Liste des articles du panier -->
            <div class="produits-panier">
                <?php foreach ($panierItems as $row):
                    $quantite  = $row['quantite'];
                    $sousTotal = $row['prix_total'];
                ?>
                    <article class="produit-panier">
                        <?php $image = !empty($row['image']) ? htmlspecialchars($row['image']) : $row['id'] . '.webp'; ?>
                        <img src="components/images_pc/<?= $image ?>" alt="<?= htmlspecialchars($row['nom']) ?>" loading="lazy">

                        <!-- Informations du produit -->
                        <div class="infos-produit">
                            <h3><?= htmlspecialchars($row['nom']) ?></h3>
                            <?php if (!empty($row['marque'])): ?>
                                <p class="marque"><?= htmlspecialchars($row['marque']) ?></p>
                            <?php endif; ?>
                            <p class="prix"><?= number_format($row['prix_unitaire'], 2, ',', ' ') ?> € / unité</p>
                            <p class="sous-total"><?= number_format($sousTotal, 2, ',', ' ') ?> €</p>
                        </div>

                        <!-- Contrôles quantité + bouton supprimer -->
                        <div class="actions">
                            <div class="qty-stepper">
                                <a href="panier.php?action=supprimer&id=<?= $row['id'] ?>" class="qty-btn qty-minus" title="Réduire" aria-label="Réduire la quantité">−</a>
                                <span class="qty-value"><?= $quantite ?></span>
                                <a href="panier.php?action=ajouter&id=<?= $row['id'] ?>" class="qty-btn qty-plus" title="Augmenter" aria-label="Augmenter la quantité">+</a>
                            </div>
                            <!-- Supprime entièrement le produit après confirmation -->
                            <a href="panier.php?action=vider&id=<?= $row['id'] ?>" class="btn-supprimer" title="Retirer du panier"
                               onclick="return confirm('Retirer ce produit du panier ?')">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                    <path d="M9 3v1H4v2h1v13a2 2 0 002 2h10a2 2 0 002-2V6h1V4h-5V3H9zm0 5h2v9H9V8zm4 0h2v9h-2V8z"/>
                                </svg>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Total du panier -->
            <div class="panier-total">Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong></div>
            </div>

            <!-- Boutons de navigation -->
            <div class="panier-actions">
                <a href="produits.php" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                    Continuer mes achats
                </a>
                <a href="paiement.php" class="btn btn-primary">
                    Procéder au paiement
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                </a>
            </div>
        </div>

        <?php
        // Libère les ressources de la requête
        $stmt->close();
        $conn->close();
        ?>
        <?php endif; ?>
    </section>
</main>

<?php require 'components/footer.php'; ?>

<script>
// Masque les alertes (succès / erreur) automatiquement après 3 secondes
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease'; // Fondu progressif
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500); // Supprime l'élément après l'animation
            });
        }, 3000); // Délai de 3 secondes avant de commencer le fondu
    }
});
</script>