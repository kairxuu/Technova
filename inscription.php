<?php
// Inscription : valide le formulaire, vérifie les doublons, crée le compte.

session_start([
    'cookie_httponly' => true,
    'cookie_secure'   => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

require_once 'db/db.php';

$errors   = [];
$formData = ['identifiant' => '', 'prenom' => '', 'nom' => '', 'mail' => '', 'telephone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'identifiant' => trim($_POST['identifiant'] ?? ''),
        'prenom'      => trim($_POST['prenom']      ?? ''),
        'nom'         => trim($_POST['nom']          ?? ''),
        'mail'        => trim($_POST['mail']         ?? ''),
        'telephone'   => trim($_POST['telephone']    ?? '')
    ];

    $password        = $_POST['mdp']         ?? '';
    $confirmPassword = $_POST['confirm_mdp'] ?? '';

    // Validation des champs
    if (empty($formData['identifiant']))                                             $errors[] = "L'identifiant est requis.";
    elseif (strlen($formData['identifiant']) < 3)                                   $errors[] = "L'identifiant doit contenir au moins 3 caractères.";

    if (empty($password))                                                            $errors[] = "Le mot de passe est requis.";
    elseif (strlen($password) < 8)                                                   $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une majuscule et un chiffre.";

    if ($password !== $confirmPassword)                                              $errors[] = "Les mots de passe ne correspondent pas.";

    if (empty($formData['mail']))                                                    $errors[] = "L'adresse email est requise.";
    elseif (!filter_var($formData['mail'], FILTER_VALIDATE_EMAIL))                  $errors[] = "L'adresse email n'est pas valide.";

    if (empty($formData['telephone']))                                               $errors[] = "Le numéro de téléphone est requis.";
    elseif (!preg_match('/^[0-9]{10}$/', $formData['telephone']))                   $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";

    if (empty($errors)) {
        try {
            // Vérifie si l'identifiant ou l'email existe déjà
            $stmt = $conn->prepare("SELECT ID_Client FROM client WHERE Identifiant = ? OR Mail = ? LIMIT 1");
            if ($stmt === false) throw new Exception("Erreur de préparation de la requête");

            $stmt->bind_param('ss', $formData['identifiant'], $formData['mail']);
            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "Un compte avec cet identifiant ou cette adresse email existe déjà.";
            } else {
                $hashedPassword = md5($password);

                $stmt = $conn->prepare("INSERT INTO client (Identifiant, MDP, Prenom, Nom, Mail, Tel) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt === false) throw new Exception("Erreur de préparation de la requête d'insertion");

                $stmt->bind_param('ssssss',
                    $formData['identifiant'], $hashedPassword,
                    $formData['prenom'], $formData['nom'],
                    $formData['mail'], $formData['telephone']
                );

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
                    header('Location: connexion.php');
                    exit();
                } else {
                    throw new Exception("Erreur lors de la création du compte.");
                }
            }
        } catch (Exception $e) {
            error_log('Erreur d\'inscription : ' . $e->getMessage());
            $errors[] = "Une erreur est survenue. Veuillez réessayer.";
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors']    = $errors;
        $_SESSION['form_data'] = $formData;
        header('Location: inscription.php');
        exit();
    }
} elseif (isset($_SESSION['form_data'])) {
    $formData = array_merge($formData, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
}

if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Page d'inscription à Technova - Créez votre compte client">
    <meta name="robots" content="noindex, nofollow">
    <title>Inscription - Technova</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'components/header.php'; ?>
</head>
<body>
    <div class="background-wrapper"></div>
    <main class="main-content">
        <section class="formulaire-inscription">
            <div class="formulaire-container">
                <h1>Créer un compte ✨</h1>
                <p class="form-subtitle">Rejoignez Technova et profitez de tous nos avantages</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" role="alert">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="inscriptionForm" class="form-group" method="POST" action="inscription.php" novalidate>
                    <div class="form-field">
                        <label for="identifiant">Identifiant <span class="required">*</span></label>
                        <input type="text" id="identifiant" name="identifiant"
                            value="<?= htmlspecialchars($formData['identifiant'], ENT_QUOTES, 'UTF-8') ?>"
                            required minlength="3" maxlength="50" autocomplete="username" aria-describedby="identifiant-help">
                        <small id="identifiant-help" class="form-hint">Minimum 3 caractères</small>
                    </div>

                    <div class="form-field">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                            value="<?= htmlspecialchars($formData['prenom'], ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="50" autocomplete="given-name">
                    </div>

                    <div class="form-field">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom"
                            value="<?= htmlspecialchars($formData['nom'], ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="50" autocomplete="family-name">
                    </div>

                    <div class="form-field">
                        <label for="mail">Adresse email <span class="required">*</span></label>
                        <input type="email" id="mail" name="mail"
                            value="<?= htmlspecialchars($formData['mail'], ENT_QUOTES, 'UTF-8') ?>"
                            required autocomplete="email" aria-describedby="email-help">
                        <small id="email-help" class="form-hint">Nous ne partagerons jamais votre email.</small>
                    </div>

                    <div class="form-field">
                        <label for="telephone">Téléphone <span class="required">*</span></label>
                        <input type="tel" id="telephone" name="telephone"
                            value="<?= htmlspecialchars($formData['telephone'], ENT_QUOTES, 'UTF-8') ?>"
                            required pattern="[0-9]{10}" title="Entrez un numéro de téléphone à 10 chiffres" autocomplete="tel">
                        <small class="form-hint">Format : 0612345678</small>
                    </div>

                    <div class="form-field">
                        <label for="mdp">Mot de passe <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="mdp" name="mdp"
                                placeholder="••••••••" required minlength="8"
                                autocomplete="new-password" aria-describedby="password-strength password-help"
                                oninput="checkPasswordStrength(this.value)">
                            <button type="button" class="toggle-password" onclick="togglePassword('mdp')" aria-label="Afficher/masquer">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-strength" class="password-strength"></div>
                        <small id="password-help" class="form-hint">Au moins 8 caractères, une majuscule et un chiffre.</small>
                    </div>

                    <div class="form-field">
                        <label for="confirm_mdp">Confirmer le mot de passe <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_mdp" name="confirm_mdp"
                                placeholder="••••••••" required minlength="8"
                                autocomplete="new-password" oninput="checkPasswordMatch()">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_mdp')" aria-label="Afficher/masquer">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small id="confirm-help" class="form-hint"></small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-user-plus"></i> Créer mon compte
                        </button>
                        <div class="login-link">
                            <p>Déjà inscrit ? <a href="connexion.php" class="text-link">Se connecter</a></p>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php require 'components/footer.php'; ?>

    <script>
    function checkPasswordStrength(password) {
        const el = document.getElementById('password-strength');
        if (!password) { el.textContent = ''; el.className = 'password-strength'; return; }
        if (password.length < 8) { el.textContent = 'Faible'; el.className = 'password-strength weak'; return; }
        const ok = /[A-Z]/.test(password) && /[0-9]/.test(password);
        el.textContent = ok ? 'Fort' : 'Moyen';
        el.className   = 'password-strength ' + (ok ? 'strong' : 'medium');
    }

    function checkPasswordMatch() {
        const pwd  = document.getElementById('mdp').value;
        const conf = document.getElementById('confirm_mdp').value;
        const help = document.getElementById('confirm-help');
        if (!conf) { help.textContent = ''; return; }
        help.textContent = pwd === conf ? 'Les mots de passe correspondent.' : 'Les mots de passe ne correspondent pas.';
        help.style.color = pwd === conf ? '#28a745' : '#dc3545';
    }

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon  = document.querySelector(`button[onclick="togglePassword('${fieldId}')"] i`);
        if (field.type === 'password') { field.type = 'text';     icon?.classList.replace('fa-eye', 'fa-eye-slash'); }
        else                           { field.type = 'password'; icon?.classList.replace('fa-eye-slash', 'fa-eye'); }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('inscriptionForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            let isValid = true;

            form.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) { field.classList.add('error'); isValid = false; }
                else                       field.classList.remove('error');
            });

            const pwd  = document.getElementById('mdp').value;
            const conf = document.getElementById('confirm_mdp').value;

            if (pwd !== conf) { document.getElementById('confirm_mdp').classList.add('error'); isValid = false; }
            else                document.getElementById('confirm_mdp').classList.remove('error');

            if (pwd.length < 8 || !/[A-Z]/.test(pwd) || !/[0-9]/.test(pwd)) {
                document.getElementById('mdp').classList.add('error'); isValid = false;
            } else {
                document.getElementById('mdp').classList.remove('error');
            }

            if (!isValid) {
                e.preventDefault();
                form.querySelector('.error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        form.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('submitBtn').click(); }
        });
    });
    </script>
</body>
</html>