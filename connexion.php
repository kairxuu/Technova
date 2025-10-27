<?php
session_start();
require_once "db.php";

$error = "";
$success = "";

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Rechercher l'utilisateur
        $sql = "SELECT ID_Client, Identifiant FROM client 
                WHERE Identifiant = '" . mysqli_real_escape_string($conn, $login) . "' 
                AND MDP = '" . mysqli_real_escape_string($conn, $password) . "'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $_SESSION["ID"] = $user["ID_Client"];
            $_SESSION["username"] = $user["Identifiant"];
            header("Location: index.php");
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Connexion</title>
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
                    <a href="connexion.php" class="nav-item active">Connexion</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="login-form-wrapper">
                <h1>Connexion</h1>

                <?php if (!empty($error)): ?>
                <div class="error-message"><?=$error?></div>
                <?php endif; ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur</label>
                        <input type="text" id="login" name="login" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn-primary">Se connecter</button>
                </form>

                <div class="auth-links">
                    <a href="inscription.php">Créer un compte</a>
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
