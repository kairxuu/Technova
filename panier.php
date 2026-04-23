<?php
// 1. Initialisation de la session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Inclusion des fonctions de base de données
require_once 'db/db.php'; // Pour la connexion $conn
require_once 'db/db_implement.php'; // Pour la fonction verifierCodePromo

// Chargement du CSS spécifique à la page panier
// (même système que produits.php — header.php injecte ce lien dans le <head>)
$extraCSS = ['/Technova/CSS/panier.css'];
$pageTitle = "Mon panier — Technova";

// 2. Initialisation du panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// 3. Gestion des actions sur le panier (ajout/suppression)
if (isset($_GET['action'])) {
    
    // Vérification de l'authentification de l'utilisateur
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = 'panier.php';
        header('Location: connexion.php');
        exit();
    }

    // Validation de l'ID du produit
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id === false) {
        $_SESSION['erreur'] = 'ID de produit invalide.';
    } else {
        // Gestion des différentes actions possibles
        switch ($_GET['action']) {
            case 'ajouter':
                // Incrémente la quantité du produit dans le panier
                $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;
                $_SESSION['toast'] = '✅ Produit ajouté au panier !';
                break;
                
            case 'supprimer':
                if (isset($_SESSION['panier'][$id])) {
                    if ($_SESSION['panier'][$id] > 1) {
                        $_SESSION['panier'][$id]--;
                        $_SESSION['toast'] = 'Quantité réduite.';
                    } else {
                        unset($_SESSION['panier'][$id]);
                        $_SESSION['toast'] = 'Produit retiré du panier.';
                    }
                }
                break;

            case 'vider':
                if (isset($_SESSION['panier'][$id])) {
                    unset($_SESSION['panier'][$id]);
                    $_SESSION['toast'] = 'Produit retiré du panier.';
                }
                break;
        }
    }

    // Redirection pour éviter le rechargement de la page (double soumission)
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'panier.php'));
    exit();
}

// Inclusion de l'en-tête du site
require_once 'components/header.php';
?>

<main class="main-content">
    <section class="panier-section">
        
        <!-- Affichage des messages de confirmation ou d'erreur -->
        <?php if (!empty($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['erreur'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['erreur']) ?></div>
            <?php unset($_SESSION['erreur']); ?>
        <?php endif; ?>

        <div class="panier-content">
            <?php if (empty($_SESSION['panier'])): ?>
                <!-- Affichage quand le panier est vide -->
                <div class="panier-vide">
                    <p>Votre panier est vide.</p>
                    <a href="produits.php" class="btn-continuer">Découvrir nos produits</a>
                </div>
            <?php else: 
                // Connexion à la base de données
                require_once 'db/db.php';
                
                // Préparation de la requête SQL pour récupérer les produits du panier
                $ids = array_keys($_SESSION['panier']);
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                
                // Requête SQL optimisée avec jointure pour récupérer les produits et leurs marques
                $query = "SELECT p.ID_PRO as id, p.Nom as nom, p.Prix as prix, p.image as image,
                                 m.Nom as marque 
                          FROM produit p 
                          LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque 
                          WHERE p.ID_PRO IN ($placeholders)";
                
                // Préparation et exécution sécurisée de la requête
                $stmt = $conn->prepare($query);
                
                // Exécution de la requête avec les paramètres (PHP 8.1+)
                // La méthode execute() gère automatiquement la liaison des paramètres
                $stmt->execute($ids);
                $result = $stmt->get_result();
                
                // Initialisation des variables
                $total = 0;
                $panierItems = []; // Pour stocker les produits avec leurs quantités

                // Parcours des résultats et calcul des totaux
                while ($row = $result->fetch_assoc()) {
                    $idProduit = $row['id'];
                    // Récupération de la quantité depuis la session
                    $quantite = isset($_SESSION['panier'][$idProduit]) ? (int)$_SESSION['panier'][$idProduit] : 1;
                    $prixUnitaire = (float)$row['prix'];
                    $prixTotalProduit = $prixUnitaire * $quantite;
                    $total += $prixTotalProduit;
                    
                    // Stocke les informations du produit pour l'affichage
                    $row['quantite'] = $quantite;
                    $row['prix_unitaire'] = $prixUnitaire;
                    $row['prix_total'] = $prixTotalProduit;
                    $panierItems[] = $row;
                }
                
                // Réinitialisation du total avant de recalculer
                $total = 0;
                
                // Calcul du total basé sur les articles du panier
                foreach ($panierItems as &$item) {
                    $total += $item['prix_total'];
                }
            ?>
            <!-- Liste des produits dans le panier -->
            <div class="produits-panier">
                <?php foreach ($panierItems as $row): 
                    $quantite = $row['quantite'];
                    $sousTotal = $row['prix_total'];
                    ?>
                        <article class="produit-panier">
                            <!-- Image du produit -->
                            <?php $image = !empty($row['image']) ? htmlspecialchars($row['image']) : $row['id'] . '.webp'; ?>
                            <img src="components/images_pc/<?= $image ?>" 
                                 alt="<?= htmlspecialchars($row['nom']) ?>"
                                 loading="lazy">
                            
                            <!-- Informations du produit -->
                            <div class="infos-produit">
                                <h3><?= htmlspecialchars($row['nom']) ?></h3>
                                <?php if (!empty($row['marque'])): ?>
                                    <p class="marque"><?= htmlspecialchars($row['marque']) ?></p>
                                <?php endif; ?>
                                <p class="prix"><?= number_format($row['prix_unitaire'], 2, ',', ' ') ?> € / unité</p>
                                <p class="sous-total"><?= number_format($sousTotal, 2, ',', ' ') ?> €</p>
                            </div>
                            
                            <!-- Actions : contrôle de quantité + suppression -->
                            <div class="actions">

                                <!-- Contrôle de quantité : bouton − / chiffre / bouton + -->
                                <div class="qty-stepper">
                                    <!-- Bouton diminuer (si quantité = 1, ça supprime le produit) -->
                                    <a href="panier.php?action=supprimer&id=<?= $row['id'] ?>"
                                       class="qty-btn qty-minus"
                                       title="Réduire la quantité"
                                       aria-label="Réduire la quantité">−</a>

                                    <!-- Affichage de la quantité actuelle -->
                                    <span class="qty-value"><?= $quantite ?></span>

                                    <!-- Bouton augmenter -->
                                    <a href="panier.php?action=ajouter&id=<?= $row['id'] ?>"
                                       class="qty-btn qty-plus"
                                       title="Augmenter la quantité"
                                       aria-label="Augmenter la quantité">+</a>
                                </div>

                                <!-- Bouton supprimer entièrement -->
                                <a href="panier.php?action=vider&id=<?= $row['id'] ?>"
                                   class="btn-supprimer"
                                   title="Retirer du panier"
                                   onclick="return confirm('Retirer ce produit du panier ?')">
                                    <!-- Icône corbeille SVG (pas besoin de Font Awesome) -->
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                        <path d="M9 3v1H4v2h1v13a2 2 0 002 2h10a2 2 0 002-2V6h1V4h-5V3H9zm0 5h2v9H9V8zm4 0h2v9h-2V8z"/>
                                    </svg>
                                </a>

                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="panier-total">
                    Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong>
                </div>
                </div>
                
                <div class="panier-actions">
                    <!-- Bouton retour aux produits -->
                    <a href="produits.php" class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                        Continuer mes achats
                    </a>
                    <!-- Bouton paiement -->
                    <a href="paiement.php" class="btn btn-primary">
                        Procéder au paiement
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 4H4c-1.11 0-2 .89-2 2v12c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                    </a>
                </div>
            </div>

            <?php 
            // Fermeture de la connexion à la base de données
            $stmt->close();
            $conn->close();
            ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php 
// Inclusion du pied de page
require 'components/footer.php'; 
?>

<script>
/**
 * Script pour gérer l'affichage des messages d'alerte
 * Fait disparaître progressivement les messages après 3 secondes
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(alert => {
                // Animation de fondu
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                
                // Suppression de l'élément après l'animation
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000); // Délai avant le début de l'animation : 3 secondes
    }
});
</script>