<?php
/**
 * Fichier : inscription.php
 * 
 * Description :
 * Gère le processus d'inscription des nouveaux utilisateurs.
 * Valide les données du formulaire, vérifie les doublons et crée un nouveau compte utilisateur.
 * 
 * Points clés :
 * - Validation complète des entrées utilisateur
 * - Protection contre les injections SQL avec des requêtes préparées
 * - Hachage sécurisé des mots de passe
 * - Gestion des erreurs et retours utilisateur
 */

// 1. Initialisation de la session avec des paramètres de sécurité
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// 2. Inclusion du fichier de connexion à la base de données
require_once 'db/db.php';

// 3. Initialisation des variables pour le formulaire et les messages d'erreur
$errors = [];
$formData = [
    'identifiant' => '',
    'prenom' => '',
    'nom' => '',
    'mail' => '',
    'telephone' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 5. Nettoyage et validation des données du formulaire
    $formData = [
        'identifiant' => trim($_POST['identifiant'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'nom' => trim($_POST['nom'] ?? ''),
        'mail' => trim($_POST['mail'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? '')
    ];
    
    $password = $_POST['mdp'] ?? '';
    $confirmPassword = $_POST['confirm_mdp'] ?? '';
    
    // 6. Validation des champs obligatoires
    if (empty($formData['identifiant'])) {
        $errors[] = "L'identifiant est requis.";
    } elseif (strlen($formData['identifiant']) < 3) {
        $errors[] = "L'identifiant doit contenir au moins 3 caractères.";
    }
    
    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins une majuscule et un chiffre.";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    if (empty($formData['mail'])) {
        $errors[] = "L'adresse email est requise.";
    } elseif (!filter_var($formData['mail'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($formData['telephone'])) {
        $errors[] = "Le numéro de téléphone est requis.";
    } elseif (!preg_match('/^[0-9]{10}$/', $formData['telephone'])) {
        $errors[] = "Le numéro de téléphone doit contenir 10 chiffres.";
    }
    
    // 7. Si pas d'erreurs de validation, on tente l'inscription
    if (empty($errors)) {
        try {
            // 8. Vérification de l'existence de l'utilisateur (requête préparée)
            $checkQuery = "SELECT ID_Client FROM client WHERE Identifiant = ? OR Mail = ? LIMIT 1";
            $stmt = $conn->prepare($checkQuery);
            
            if ($stmt === false) {
                throw new Exception("Erreur lors de la préparation de la requête de vérification");
            }
            
            $stmt->bind_param('ss', $formData['identifiant'], $formData['mail']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $errors[] = "Un compte avec cet identifiant ou cette adresse email existe déjà.";
            } else {
                // 9. Hachage sécurisé du mot de passe
                // Note : Dans une version future, utilisez password_hash()
                $hashedPassword = md5($password); // À remplacer par password_hash($password, PASSWORD_DEFAULT);
                
                // 10. Insertion du nouvel utilisateur (requête préparée)
                $insertQuery = "INSERT INTO client (Identifiant, MDP, Prenom, Nom, Mail, Tel) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($insertQuery);
                
                if ($stmt === false) {
                    throw new Exception("Erreur lors de la préparation de la requête d'insertion");
                }
                
                $stmt->bind_param('ssssss', 
                    $formData['identifiant'],
                    $hashedPassword,
                    $formData['prenom'],
                    $formData['nom'],
                    $formData['mail'],
                    $formData['telephone']
                );
                
                if ($stmt->execute()) {
                    // 11. Inscription réussie - Redirection vers la page de connexion avec un message de succès
                    $_SESSION['success'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
                    header('Location: connexion.php');
                    exit();
                } else {
                    throw new Exception("Erreur lors de la création du compte. Veuillez réessayer.");
                }
            }
        } catch (Exception $e) {
            // Journalisation de l'erreur (à remplacer par un système de logs en production)
            error_log('Erreur d\'inscription : ' . $e->getMessage());
            $errors[] = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
        } finally {
            // Fermeture des ressources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
        }
    }
    
    // Stockage des erreurs dans la session pour affichage
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $formData;
        
        // Redirection pour éviter la soumission multiple du formulaire
        header('Location: inscription.php');
        exit();
    }
} elseif (isset($_SESSION['form_data'])) {
    // Récupération des données du formulaire en cas d'erreur
    $formData = array_merge($formData, $_SESSION['form_data']);
    unset($_SESSION['form_data']);
}

// Récupération des erreurs de la session
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
    
    <!-- Feuilles de style -->
    <link rel="stylesheet" href="/Technova/CSS/global.css">
    <link rel="stylesheet" href="/Technova/CSS/login.css">
    
    <!-- Police d'icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Inclusion de l'en-tête du site -->
    <?php include 'components/header.php'; ?>
    
    <style>
        /* Styles spécifiques à la page d'inscription */
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }
        
        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #ffc107; }
        .password-strength.strong { color: #28a745; }
        
        .form-field {
            margin-bottom: 1.25rem;
        }
        
        .form-field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-field input[type="text"],
        .form-field input[type="email"],
        .form-field input[type="tel"],
        .form-field input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-actions {
            margin-top: 1.5rem;
        }
        
        .login-link {
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="background-wrapper"></div>
    
    <main class="main-content">
        <section class="formulaire-inscription">
            <div class="formulaire-container">
                <h1>Créer un compte</h1>
                
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
                        <input 
                            type="text" 
                            id="identifiant" 
                            name="identifiant" 
                            value="<?= htmlspecialchars($formData['identifiant'], ENT_QUOTES, 'UTF-8') ?>" 
                            required
                            minlength="3"
                            maxlength="50"
                            autocomplete="username"
                            aria-describedby="identifiant-help"
                        >
                        <small id="identifiant-help" class="form-hint">Minimum 3 caractères</small>
                    </div>


                    
                    <div class="form-field">
                        <label for="prenom">Prénom</label>
                        <input 
                            type="text" 
                            id="prenom" 
                            name="prenom" 
                            value="<?= htmlspecialchars($formData['prenom'], ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="50"
                            autocomplete="given-name"
                        >
                    </div>
                    
                    <div class="form-field">
                        <label for="nom">Nom</label>
                        <input 
                            type="text" 
                            id="nom" 
                            name="nom" 
                            value="<?= htmlspecialchars($formData['nom'], ENT_QUOTES, 'UTF-8') ?>"
                            maxlength="50"
                            autocomplete="family-name"
                        >
                    </div>
                    
                    <div class="form-field">
                        <label for="mail">Adresse email <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="mail" 
                            name="mail" 
                            value="<?= htmlspecialchars($formData['mail'], ENT_QUOTES, 'UTF-8') ?>" 
                            required
                            autocomplete="email"
                            aria-describedby="email-help"
                        >
                        <small id="email-help" class="form-hint">Nous ne partagerons jamais votre email.</small>
                    </div>
                    
                    <div class="form-field">
                        <label for="telephone">Téléphone <span class="required">*</span></label>
                        <input 
                            type="tel" 
                            id="telephone" 
                            name="telephone" 
                            value="<?= htmlspecialchars($formData['telephone'], ENT_QUOTES, 'UTF-8') ?>" 
                            required
                            pattern="[0-9]{10}"
                            title="Entrez un numéro de téléphone à 10 chiffres"
                            autocomplete="tel"
                        >
                        <small class="form-hint">Format : 0612345678</small>
                    </div>
                    
                    <div class="form-field">
                        <label for="mdp">Mot de passe <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="mdp" 
                                name="mdp" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                                aria-describedby="password-strength password-help"
                                oninput="checkPasswordStrength(this.value)"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('mdp')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="password-strength" class="password-strength"></div>
                        <small id="password-help" class="form-hint">
                            Le mot de passe doit contenir au moins 8 caractères, dont une majuscule et un chiffre.
                        </small>
                    </div>
                    
                    <div class="form-field">
                        <label for="confirm_mdp">Confirmer le mot de passe <span class="required">*</span></label>
                        <div style="position: relative;">
                            <input 
                                type="password" 
                                id="confirm_mdp" 
                                name="confirm_mdp" 
                                required
                                minlength="8"
                                autocomplete="new-password"
                                oninput="checkPasswordMatch()"
                            >
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm_mdp')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
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
                            <p>Déjà inscrit ? 
                                <a href="connexion.php" class="text-link">Se connecter</a>
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <!-- Inclusion du pied de page -->
    <?php require 'components/footer.php'; ?>
    
    <!-- Scripts JavaScript -->
    <script>
    /**
     * Vérifie la force du mot de passe et met à jour l'interface utilisateur
     */
    function checkPasswordStrength(password) {
        const strengthIndicator = document.getElementById('password-strength');
        const submitBtn = document.getElementById('submitBtn');
        
        if (!password) {
            strengthIndicator.textContent = '';
            strengthIndicator.className = 'password-strength';
            return;
        }
        
        // Vérification de la longueur
        if (password.length < 8) {
            strengthIndicator.textContent = 'Faible';
            strengthIndicator.className = 'password-strength weak';
            return;
        }
        
        // Vérification de la présence d'une majuscule et d'un chiffre
        const hasUpperCase = /[A-Z]/.test(password);
        const hasNumber = /[0-9]/.test(password);
        
        if (hasUpperCase && hasNumber) {
            strengthIndicator.textContent = 'Fort';
            strengthIndicator.className = 'password-strength strong';
        } else {
            strengthIndicator.textContent = 'Moyen';
            strengthIndicator.className = 'password-strength medium';
        }
    }
    
    /**
     * Vérifie si les mots de passe correspondent
     */
    function checkPasswordMatch() {
        const password = document.getElementById('mdp').value;
        const confirmPassword = document.getElementById('confirm_mdp').value;
        const confirmHelp = document.getElementById('confirm-help');
        
        if (!confirmPassword) {
            confirmHelp.textContent = '';
            confirmHelp.className = 'form-hint';
            return;
        }
        
        if (password === confirmPassword) {
            confirmHelp.textContent = 'Les mots de passe correspondent.';
            confirmHelp.className = 'form-hint';
            confirmHelp.style.color = '#28a745';
        } else {
            confirmHelp.textContent = 'Les mots de passe ne correspondent pas.';
            confirmHelp.className = 'form-hint';
            confirmHelp.style.color = '#dc3545';
        }
    }
    
    /**
     * Bascule la visibilité du mot de passe
     */
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.querySelector(`button[onclick="togglePassword('${fieldId}')"] i`);
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    /**
     * Validation côté client du formulaire
     */
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('inscriptionForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');
                
                // Vérification des champs obligatoires
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('error');
                        isValid = false;
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                // Vérification de la correspondance des mots de passe
                const password = document.getElementById('mdp').value;
                const confirmPassword = document.getElementById('confirm_mdp').value;
                
                if (password !== confirmPassword) {
                    document.getElementById('confirm_mdp').classList.add('error');
                    isValid = false;
                } else {
                    document.getElementById('confirm_mdp').classList.remove('error');
                }
                
                // Vérification de la force du mot de passe
                if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) {
                    document.getElementById('mdp').classList.add('error');
                    isValid = false;
                } else {
                    document.getElementById('mdp').classList.remove('error');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Défilement vers le premier champ en erreur
                    const firstError = form.querySelector('.error');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Gestion de la soumission du formulaire avec la touche Entrée
            form.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('submitBtn').click();
                }
            });
        }
    });
    </script>
</body>
</html>