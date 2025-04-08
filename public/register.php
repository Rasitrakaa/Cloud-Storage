<?php
session_start();
require '../config/db.php';

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = "Veuillez entrer votre email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "Cet email est déjà utilisé par un autre compte.";
        }
    }
    
    if (empty($password)) {
        $errors['password'] = "Veuillez choisir un mot de passe.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    if ($password != $password_confirm) {
        $errors['password_confirm'] = "Les mots de passe ne correspondent pas.";
    }
    
    // Création du compte si pas d'erreurs
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$email, $hashed_password]);
            
            // Créer un dossier pour l'utilisateur
            $user_id = $pdo->lastInsertId();
            $upload_dir = '../uploads/' . $user_id;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Message de succès et redirection
            $_SESSION['register_success'] = true;
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudStorage - Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }
        
        .form-signup {
            max-width: 440px;
            padding: 15px;
        }
        
        .form-signup .form-floating:focus-within {
            z-index: 2;
        }
        
        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
        }
        
        .btn-primary {
            font-weight: 600;
            padding: 0.75rem 1rem;
        }
        
        .password-strength {
            height: 5px;
            transition: all 0.3s ease;
        }
        
        .terms-text {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container form-signup w-100 m-auto">
        <div class="card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h1 class="h3 mb-0"><i class="bi bi-cloud-fill me-2"></i>CloudStorage</h1>
                <p class="mb-0">Stockage sécurisé pour vos fichiers</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Créer un compte</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" novalidate>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" placeholder="nom@exemple.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                        <label for="email"><i class="bi bi-envelope me-1"></i>Adresse email</label>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-floating mb-1">
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Mot de passe" required>
                        <label for="password"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <div class="password-strength w-100 bg-light rounded"></div>
                        <small id="passwordHelpBlock" class="form-text text-muted">
                            Le mot de passe doit contenir au moins 8 caractères.
                        </small>
                    </div>
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" id="password_confirm" name="password_confirm" placeholder="Confirmer le mot de passe" required>
                        <label for="password_confirm"><i class="bi bi-lock-fill me-1"></i>Confirmer le mot de passe</label>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <div class="invalid-feedback"><?= $errors['password_confirm'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                        <label class="form-check-label terms-text" for="terms">
                            J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">conditions d'utilisation</a> et la <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">politique de confidentialité</a>
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" type="submit" id="submitBtn" disabled>
                            <i class="bi bi-person-plus me-2"></i>Créer mon compte
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light py-3 text-center">
                <p class="mb-0">Déjà un compte ? <a href="login.php" class="fw-bold text-decoration-none">Se connecter</a></p>
            </div>
        </div>
        
        <div class="text-center mt-3 text-muted">
            <small>© <?= date('Y') ?> CloudStorage</small>
        </div>
    </div>
    
    <!-- Modal des conditions d'utilisation -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conditions d'utilisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Bienvenue sur CloudStorage. En utilisant notre service, vous acceptez de respecter ces conditions.</p>
                    <p>1. <strong>Compte utilisateur</strong> : Vous êtes responsable de maintenir la confidentialité de votre compte.</p>
                    <p>2. <strong>Contenu</strong> : Vous ne devez pas télécharger de contenu illégal ou violant les droits d'autrui.</p>
                    <p>3. <strong>Utilisation du service</strong> : Nous nous réservons le droit de limiter ou de résilier votre accès en cas d'abus.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de politique de confidentialité -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Politique de confidentialité</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Chez CloudStorage, nous prenons la protection de vos données personnelles très au sérieux.</p>
                    <p>1. <strong>Données collectées</strong> : Nous collectons votre adresse email et stockons vos fichiers de manière chiffrée.</p>
                    <p>2. <strong>Sécurité</strong> : Vos fichiers sont chiffrés avec AES-256 pour garantir leur confidentialité.</p>
                    <p>3. <strong>Accès aux données</strong> : Nous ne partagerons jamais vos données avec des tiers sans votre consentement.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Vérification de la correspondance des mots de passe
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirm');
        const termsCheckbox = document.getElementById('terms');
        const submitBtn = document.getElementById('submitBtn');
        const passwordStrength = document.querySelector('.password-strength');
        
        // Fonction pour vérifier si le formulaire est valide
        function checkFormValidity() {
            const isPasswordValid = password.value.length >= 8;
            const doPasswordsMatch = password.value === confirmPassword.value;
            const areTermsAccepted = termsCheckbox.checked;
            
            submitBtn.disabled = !(isPasswordValid && doPasswordsMatch && areTermsAccepted);
        }
        
        // Vérifier la force du mot de passe
        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            
            if (value.length >= 8) strength += 25;
            if (value.match(/[A-Z]/)) strength += 25;
            if (value.match(/[0-9]/)) strength += 25;
            if (value.match(/[^A-Za-z0-9]/)) strength += 25;
            
            passwordStrength.style.width = strength + '%';
            
            if (strength <= 25) {
                passwordStrength.style.backgroundColor = '#dc3545'; // rouge
            } else if (strength <= 50) {
                passwordStrength.style.backgroundColor = '#ffc107'; // jaune
            } else if (strength <= 75) {
                passwordStrength.style.backgroundColor = '#0dcaf0'; // bleu clair
            } else {
                passwordStrength.style.backgroundColor = '#198754'; // vert
            }
            
            checkFormValidity();
        });
        
        // Vérifier la correspondance des mots de passe
        confirmPassword.addEventListener('input', checkFormValidity);
        
        // Vérifier les conditions d'utilisation
        termsCheckbox.addEventListener('change', checkFormValidity);
    </script>
</body>
</html>