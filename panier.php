<?php
// Technova - Panier d'achat simplifié
session_start();
require_once "db.php";

// Messages utilisateur
$success_message = "";
$error_message = "";

// Traitement des actions du panier
if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0) {
    $user_id = $_SESSION["ID"];

    // Ajouter un produit
    if (isset($_GET["idpro"])) {
        $product_id = (int)$_GET["idpro"];

        // Vérifier que le produit existe
        $sql = "SELECT ID_PRO FROM produit WHERE ID_PRO = $product_id";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            // Vérifier s'il est déjà dans le panier
            $sql = "SELECT Qte FROM panier WHERE ID_Client = $user_id AND ID_PRO = $product_id";
            $result = mysqli_query($conn, $sql);
            $existing = mysqli_fetch_array($result, MYSQLI_ASSOC);

            if ($existing) {
                // Augmenter la quantité
                $sql = "UPDATE panier SET Qte = Qte + 1 WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                mysqli_query($conn, $sql);
                $success_message = "Quantité mise à jour !";
            } else {
                // Ajouter au panier
                $sql = "INSERT INTO panier (ID_Client, ID_PRO, Qte) VALUES ($user_id, $product_id, 1)";
                mysqli_query($conn, $sql);
                $success_message = "Produit ajouté au panier !";
            }
        } else {
            $error_message = "Produit introuvable.";
        }
    }

    // Actions sur le panier
    if (isset($_GET["action"]) && isset($_GET["idpro"])) {
        $action = $_GET["action"];
        $product_id = (int)$_GET["idpro"];

        switch ($action) {
            case "increase":
                $sql = "UPDATE panier SET Qte = Qte + 1 WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                mysqli_query($conn, $sql);
                break;

            case "decrease":
                $sql = "SELECT Qte FROM panier WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                $result = mysqli_query($conn, $sql);
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if ($row && $row['Qte'] > 1) {
                    $sql = "UPDATE panier SET Qte = Qte - 1 WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                    mysqli_query($conn, $sql);
                } else {
                    $sql = "DELETE FROM panier WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                    mysqli_query($conn, $sql);
                }
                break;

            case "remove":
                $sql = "DELETE FROM panier WHERE ID_Client = $user_id AND ID_PRO = $product_id";
                mysqli_query($conn, $sql);
                break;
        }

        // Redirection pour éviter la re-soumission
        header("Location: panier.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Mon Panier</title>
    <link href="CSS/general.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <nav class="top-nav">
            <div class="nav-container">
                <a href="index.php" class="nav-brand">Technova</a>
                <div class="nav-menu">
                    <a href="index.php" class="nav-item">Accueil</a>
                    <a href="produits.php" class="nav-item">Produits</a>
                    <a href="contact.php" class="nav-item">Contact</a>
                    <a href="panier.php" class="nav-item active" style="font-size: 1.2rem;">🛒</a>
                    <?php if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0): ?>
                        <span class="nav-item user-info">👤 <?=$_SESSION["username"]?></span>
                        <a href="logout.php" class="nav-item logout-btn">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="nav-item login-btn">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <h1>Mon Panier</h1>

            <?php if (!empty($success_message)): ?>
            <div class="success-message"><?=$success_message?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="error-message"><?=$error_message?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0): ?>
                <?php
                // Récupérer le contenu du panier
                $user_id = $_SESSION["ID"];
                $sql = "SELECT p.ID_PRO, p.Nom, p.Prix, m.Nom as marque, pa.Qte
                        FROM panier pa
                        JOIN produit p ON pa.ID_PRO = p.ID_PRO
                        LEFT JOIN marque m ON p.ID_Marque = m.ID_Marque
                        WHERE pa.ID_Client = $user_id
                        ORDER BY p.Nom";

                $result = mysqli_query($conn, $sql);
                ?>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="panier-grid">
                        <?php
                        $total = 0;
                        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                            $product_id = $row['ID_PRO'];
                            $nom = $row['Nom'];
                            $prix = $row['Prix'];
                            $marque = $row['marque'] ?? 'Non spécifiée';
                            $quantite = $row['Qte'];
                            $sous_total = $prix * $quantite;
                            $total += $sous_total;
                        ?>
                        <div class="panier-item">
                            <div class="panier-item-image">
                                <img src="assets/<?=$product_id?>.webp" alt="<?=$nom?>" onerror="this.src='assets/no-image.png'">
                            </div>
                            <div class="panier-item-info">
                                <h3><?=$nom?></h3>
                                <p class="brand"><?=$marque?></p>
                                <p class="price"><?=$prix?> €</p>
                                <div class="quantity-controls">
                                    <a href="panier.php?action=decrease&idpro=<?=$product_id?>" class="btn-quantity">-</a>
                                    <span><?=$quantite?></span>
                                    <a href="panier.php?action=increase&idpro=<?=$product_id?>" class="btn-quantity">+</a>
                                </div>
                                <p class="subtotal"><?=$sous_total?> €</p>
                                <a href="panier.php?action=remove&idpro=<?=$product_id?>" class="btn-remove">Supprimer</a>
                            </div>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="panier-total">
                        <h3>Total: <?=$total?> €</h3>
                        <div class="panier-actions">
                            <a href="produits.php" class="btn-secondary">Continuer les achats</a>
                            <a href="index.php" class="btn-primary">Procéder au paiement</a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="panier-empty">
                        <h3>Votre panier est vide</h3>
                        <p>Ajoutez des produits en parcourant notre catalogue.</p>
                        <a href="produits.php" class="btn-primary">Découvrir nos produits</a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="panier-login-required">
                    <h3>Connectez-vous pour voir votre panier</h3>
                    <p>Vous devez être connecté pour accéder à votre panier.</p>
                    <div class="auth-links">
                        <a href="connexion.php" class="btn-primary">Se connecter</a>
                        <a href="inscription.php" class="btn-secondary">Créer un compte</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-section">
            <h3>À propos</h3>
            <p>Technova - Technologies innovantes</p>
            <p>&copy; 2024 Technova</p>
        </div>
    </footer>
</body>
</html>