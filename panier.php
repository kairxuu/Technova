<?php
/**
 * Fichier : panier.php
 * Gestion du panier d'achat
 * 
 * Fonctionnalités :
 * - Affichage des produits ajoutés au panier
 * - Ajout/Suppression de produits
 * - Calcul du total des achats
 * - Redirection vers la page de paiement
 */

// 1. Initialisation de la session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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
                $_SESSION['message'] = 'Produit ajouté au panier.';
                break;
                
            case 'supprimer':
                // Supprime le produit du panier
                if (isset($_SESSION['panier'][$id])) {
                    unset($_SESSION['panier'][$id]);
                    $_SESSION['message'] = 'Produit retiré du panier.';
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
                $query = "SELECT p.ID_PRO as id, p.Nom as nom, p.Prix as prix, 
                                 m.Nom as marque 
                          FROM produit p 
                          LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque 
                          WHERE p.ID_PRO IN ($placeholders)";
                
                // Préparation et exécution sécurisée de la requête
                $stmt = $conn->prepare($query);
                
                // Construction dynamique des paramètres pour bind_param
                $types = str_repeat('i', count($ids));
                $bindParams = [&$types];
                
                // Ajout des références des IDs comme paramètres
                foreach ($ids as &$id) {
                    $bindParams[] = &$id;
                }
                
                // Appel dynamique de bind_param avec les paramètres
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
                $stmt->execute();
                $result = $stmt->get_result();
                $total = 0;
            ?>
                <!-- Liste des produits dans le panier -->
                <div class="produits-panier">
                    <?php while ($produit = $result->fetch_assoc()): 
                        // Calcul de la quantité et du sous-total pour chaque produit
                        $quantite = $_SESSION['panier'][$produit['id']];
                        $sousTotal = $produit['prix'] * $quantite;
                        $total += $sousTotal;
                    ?>
                        <article class="produit-panier">
                            <!-- Image du produit -->
                            <img src="components/Images/<?= $produit['id'] ?>.webp" 
                                 alt="<?= htmlspecialchars($produit['nom']) ?>"
                                 loading="lazy">
                            
                            <!-- Informations du produit -->
                            <div class="infos-produit">
                                <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                                <?php if (!empty($produit['marque'])): ?>
                                    <p class="marque">Marque : <?= htmlspecialchars($produit['marque']) ?></p>
                                <?php endif; ?>
                                <p class="prix">Prix unitaire : <?= number_format($produit['prix'], 2, ',', ' ') ?> €</p>
                                <p class="quantite">Quantité : <?= $quantite ?></p>
                                <p class="sous-total">Sous-total : <?= number_format($sousTotal, 2, ',', ' ') ?> €</p>
                            </div>
                            
                            <!-- Actions possibles sur le produit -->
                            <div class="actions">
                                <a href="panier.php?action=ajouter&id=<?= $produit['id'] ?>" 
                                   class="btn-ajouter" 
                                   title="Ajouter un exemplaire">
                                    <i class="fas fa-plus"></i>
                                </a>
                                <a href="panier.php?action=supprimer&id=<?= $produit['id'] ?>" 
                                   class="btn-supprimer" 
                                   title="Supprimer du panier"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                    <i class="fas fa-trash"></i>
                                    <span class="sr-only">Supprimer</span>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <!-- Récapitulatif du panier -->
                <div class="panier-resume">
                    <div class="panier-total">
                        <p>Total des achats : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong></p>
                    </div>
                    
                    <div class="panier-actions">
                        <a href="produits.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Continuer mes achats
                        </a>
                        <a href="paiement.php" class="btn btn-primary">
                            Procéder au paiement <i class="fas fa-credit-card"></i>
                        </a>
                    </div>
                </div>
            <?php 
                // Fermeture de la connexion à la base de données
                $stmt->close();
                $conn->close();
            endif; 
            ?>
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