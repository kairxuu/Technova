<?php
/**
 * Fichier : panier.php
 * Description : Affiche le contenu du panier de l'utilisateur connecté
 * Accès restreint aux utilisateurs authentifiés
 */

// 1. Gestion de la session
// Vérification si une session est déjà active avant d'en démarrer une nouvelle
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 2. Vérification de l'authentification
// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    // On enregistre la page actuelle pour y rediriger l'utilisateur après connexion
    $_SESSION['redirect_after_login'] = 'panier.php';
    header('Location: connexion.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Métadonnées de la page -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - TN Commerce</title>
    
    <!-- Inclusion des feuilles de style -->
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/panier.css">
    <link rel="stylesheet" href="/Technova/CSS/general.css">
    <link rel="stylesheet" href="/Technova/CSS/components.css">
</head>

<!-- Inclusion de l'en-tête du site -->
<?php include 'components/header.php'; ?>

<body>
    <!-- Section principale du panier -->
    <section class="panier-section">
        <h1>Votre Panier</h1>
        
        <!-- Contenu du panier -->
        <div class="panier-content">
            <?php
            // Vérification si le panier existe et n'est pas vide
            if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
                // Connexion à la base de données
                require 'db/db.php';
                
                // Récupération des IDs des produits du panier
                $ids = array_keys($_SESSION['panier']);
                $ids_list = implode(',', $ids);
                
                // Récupération des informations des produits depuis la base de données
                $sql = "SELECT * FROM produits WHERE ID_Produit IN ($ids_list)";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    // Affichage du tableau des produits
                    echo '<table class="panier-table">';
                    echo '<thead><tr>';
                    echo '<th>Produit</th>';
                    echo '<th>Prix unitaire</th>';
                    echo '<th>Quantité</th>';
                    echo '<th>Total</th>';
                    echo '<th>Actions</th>';
                    echo '</tr></thead>';
                    echo '<tbody>';
                    
                    $total_panier = 0;
                    
                    // Affichage de chaque produit du panier
                    while($row = $result->fetch_assoc()) {
                        $id_produit = $row['ID_Produit'];
                        $quantite = $_SESSION['panier'][$id_produit];
                        $total = $row['Prix'] * $quantite;
                        $total_panier += $total;
                        
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['Nom']) . '</td>';
                        echo '<td>' . number_format($row['Prix'], 2, ',', ' ') . ' €</td>';
                        echo '<td>' . $quantite . '</td>';
                        echo '<td>' . number_format($total, 2, ',', ' ') . ' €</td>';
                        echo '<td>';
                        echo '<a href="modifier_quantite.php?id=' . $id_produit . '&action=plus">+</a> ';
                        echo '<a href="modifier_quantite.php?id=' . $id_produit . '&action=moins">-</a> ';
                        echo '<a href="supprimer_produit.php?id=' . $id_produit . '" class="supprimer">Supprimer</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    // Affichage du total du panier
                    echo '<tr class="total">';
                    echo '<td colspan="3"><strong>Total du panier</strong></td>';
                    echo '<td colspan="2"><strong>' . number_format($total_panier, 2, ',', ' ') . ' €</strong></td>';
                    echo '</tr>';
                    
                    echo '</tbody></table>';
                    
                    // Bouton de validation du panier
                    echo '<div class="panier-actions">';
                    echo '<a href="valider_commande.php" class="btn-valider">Valider ma commande</a>';
                    echo '</div>';
                    
                } else {
                    // Message si le panier contient des produits invalides
                    echo '<p>Certains produits de votre panier ne sont plus disponibles.</p>';
                    // On vide le panier des produits invalides
                    $_SESSION['panier'] = [];
                }
                
                $conn->close();
                
            } else {
                // Message si le panier est vide
                echo '<p>Votre panier est actuellement vide.</p>';
                echo '<p><a href="produits.php" class="btn-continuer">Continuer vos achats</a></p>';
            }
            ?>
        </div>
    </section>

    <!-- Inclusion du pied de page -->
    <?php require 'components/footer.php'; ?>

    <!-- Scripts JavaScript -->
    <script>
    // Fonction pour confirmer la suppression d'un produit du panier
    function confirmerSuppression(event) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce produit de votre panier ?')) {
            event.preventDefault();
        }
    }
    
    // Ajout des écouteurs d'événements pour les liens de suppression
    document.addEventListener('DOMContentLoaded', function() {
        var liensSuppression = document.querySelectorAll('a.supprimer');
        liensSuppression.forEach(function(lien) {
            lien.addEventListener('click', confirmerSuppression);
        });
    });
    </script>
</body>
</html>