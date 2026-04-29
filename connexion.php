<?php
// Authentification : vérifie les identifiants, crée la session, redirige.

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

require_once 'db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'];

    $errors = [];

    if (empty($email))    $errors[] = "L'identifiant ou l'email est requis.";
    if (empty($password)) $errors[] = "Le mot de passe est requis.";

    if (empty($errors)) {
        try {
            // Recherche par mail ou identifiant
            $stmt = $conn->prepare("SELECT ID_Client, Identifiant, MDP FROM client WHERE Mail = ? OR Identifiant = ? LIMIT 1");
            if ($stmt === false) throw new Exception("Erreur de préparation de la requête");

            $stmt->bind_param('ss', $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (md5($password) === $user['MDP']) {
                    $_SESSION['user_id']  = $user['ID_Client'];
                    $_SESSION['username'] = $user['Identifiant'];
                    session_regenerate_id(true);

                    $redirect = 'index.php';
                    if (isset($_SESSION['redirect_after_login']) &&
                        strpos($_SESSION['redirect_after_login'], 'connexion.php') === false) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                    }

                    $_SESSION['toast'] = '👋 Bienvenue, ' . htmlspecialchars($user['Identifiant']) . ' !';
                    header('Location: ' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'));
                    exit();
                }
            }

            $errors[] = "Identifiants incorrects ou compte inexistant.";

        } catch (Exception $e) {
            error_log('Erreur de connexion : ' . $e->getMessage());
            $errors[] = "Une erreur est survenue. Veuillez réessayer.";
        }
    }

    $_SESSION['error']     = implode('<br>', $errors);
    $_SESSION['form_data'] = ['email' => $email];
    header('Location: connexion.php');
    exit();
}

// Récupération des données de session après erreur
$formData = $_SESSION['form_data'] ?? [];
$error    = $_SESSION['error'] ?? '';
unset($_SESSION['form_data'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page de connexion à votre compte Technova">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion - Technova</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
    <?php include 'components/header.php'; ?>
</head>
<body>
    <div class="background-wrapper"></div>

    <main class="main-content">
        <section class="formulaire-connexion">
            <div class="formulaire-container">
                <h1>Bon retour 👋</h1>
                <p class="form-subtitle">Connectez-vous à votre compte Technova</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form class="form-group" method="POST" action="connexion.php" novalidate>
                    <div class="form-field">
                        <label for="email">Identifiant ou Email</label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            placeholder="votre identifiant ou email"
                            value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            required
                            autocomplete="username"
                            aria-describedby="email-help"
                        >
                        <small id="email-help" class="form-hint">Nous ne partagerons jamais votre email.</small>
                    </div>

                    <div class="form-field">
                        <label for="password">Mot de passe</label>
                        <div style="position: relative;">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                aria-describedby="password-help"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('password')" aria-label="Afficher/masquer le mot de passe">
                                <i class="fas fa-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                        <small id="password-help" class="form-hint">
                            <a href="mot-de-passe-oublie.php">Mot de passe oublié ?</a>
                        </small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                        <div class="form-footer">
                            <p>Pas encore de compte ?
                                <a href="inscription.php" class="text-link">Créer un compte</a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php require 'components/footer.php'; ?>

    <script>
    // Toggle visibilité mot de passe
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon  = field.parentElement.querySelector('.toggle-password i');
        if (field.type === 'password') {
            field.type = 'text';
            if (icon) icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            if (icon) icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Validation côté client
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const email    = document.getElementById('email');
            const password = document.getElementById('password');
            let isValid    = true;

            if (!email.value)    { email.classList.add('error');    isValid = false; }
            else                   email.classList.remove('error');

            if (!password.value) { password.classList.add('error'); isValid = false; }
            else                   password.classList.remove('error');

            if (!isValid) e.preventDefault();
        });
    });
    </script>
</body>
</html>