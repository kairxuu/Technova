<?php
session_start();
require_once "db.php";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Contact</title>
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
                    <a href="contact.php" class="nav-item active">Contact</a>
                    <a href="panier.php" class="nav-item" style="font-size: 1.2rem;">🛒</a>
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

    <!-- Contenu principal -->
    <main class="main-content">
        <div class="container">
            <div class="contact-page">
                <h1 class="page-title">Contactez-nous</h1>
                <p class="page-subtitle">Nous sommes là pour répondre à toutes vos questions</p>

                <div class="contact-grid">
                    <!-- Informations de contact -->
                    <div class="contact-info">
                        <h2>Nos coordonnées</h2>
                        <div class="contact-item">
                            <strong>Email :</strong>
                            <p>contact@technova.com</p>
                        </div>
                        <div class="contact-item">
                            <strong>Téléphone :</strong>
                            <p>+33 1 23 45 67 89</p>
                        </div>
                        <div class="contact-item">
                            <strong>Adresse :</strong>
                            <p>123 Avenue de la Technologie<br>75001 Paris, France</p>
                        </div>
                        <div class="contact-item">
                            <strong>Horaires :</strong>
                            <p>Lundi - Vendredi : 9h - 18h<br>Samedi : 10h - 17h</p>
                        </div>
                    </div>

                    <!-- Formulaire de contact -->
                    <div class="contact-form">
                        <h2>Envoyez-nous un message</h2>
                        <form action="send-contact.php" method="POST">
                            <div class="form-group">
                                <label for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="sujet">Sujet</label>
                                <select id="sujet" name="sujet">
                                    <option value="question">Question générale</option>
                                    <option value="support">Support technique</option>
                                    <option value="retour">Retour produit</option>
                                    <option value="partenariat">Partenariat</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn-primary">Envoyer le message</button>
                        </form>
                    </div>
                </div>
            </div>
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
