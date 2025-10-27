<?php
// Technova - Page d'inscription
session_start();
require_once "db.php";

// Variables pour les messages
$error = "";
$success = "";

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = trim($_POST["login"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    // Validation basique
    if (empty($login) || empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        // Vérifier si le login existe déjà
        $sql = "SELECT ID_Client FROM client WHERE Identifiant = '" . mysqli_real_escape_string($conn, $login) . "'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            $error = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // Créer le compte
            $sql = "INSERT INTO client (Identifiant, MDP, Mail, Nom, Tel, Prenom)
                    VALUES ('" . mysqli_real_escape_string($conn, $login) . "',
                           '" . mysqli_real_escape_string($conn, $password) . "',
                           " . (!empty($email) ? "'" . mysqli_real_escape_string($conn, $email) . "'" : "NULL") . ",
                           '" . mysqli_real_escape_string($conn, $login) . "',
                           '',
                           '')";

            if (mysqli_query($conn, $sql)) {
                $success = "Compte créé avec succès ! Vous êtes maintenant connecté.";
                $_SESSION["ID"] = mysqli_insert_id($conn);
                $_SESSION["username"] = $login;
            } else {
                $error = "Erreur lors de la création du compte. Veuillez réessayer.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technova - Inscription</title>
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
                    <a href="panier.php" class="nav-item" style="font-size: 1.2rem;">🛒</a>
                    <?php if (isset($_SESSION["ID"]) && $_SESSION["ID"] > 0): ?>
                        <span class="nav-item user-info">👤 <?=$_SESSION["username"]?></span>
                        <a href="logout.php" class="nav-item logout-btn">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="nav-item login-btn active">Connexion</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="login-form-wrapper">
                <h1>Inscription</h1>

                <?php if (!empty($error)): ?>
                <div class="error-message"><?=$error?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                <div class="success-message"><?=$success?></div>
                <a href="index.php" class="btn-primary">Aller à l'accueil</a>
                <?php else: ?>

                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="login">Nom d'utilisateur *</label>
                        <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn-primary">Créer mon compte</button>
                </form>

                <div class="auth-links">
                    <a href="connexion.php">Déjà un compte ? Se connecter</a>
                </div>

                <?php endif; ?>
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
