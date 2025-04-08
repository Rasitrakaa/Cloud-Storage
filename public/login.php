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
    
    // Validation basique
    $errors = [];
    if (empty($email)) {
        $errors['email'] = "Veuillez entrer votre email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format d'email invalide.";
    }
    
    if (empty($password)) {
        $errors['password'] = "Veuillez entrer votre mot de passe.";
    }
    
    // Vérification des identifiants si pas d'erreurs
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            
            // Se souvenir de l'utilisateur
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // Stocker le token en base de données
                $stmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expires]);
                
                // Créer un cookie
                setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
            }
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudStorage - Connexion</title>
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
        
        .form-signin {
            max-width: 440px;
            padding: 15px;
        }
        
        .form-signin .form-floating:focus-within {
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
        
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .invalid-feedback {
            font-size: 0.875em;
        }
    </style>
</head>
<body>
    <div class="container form-signin w-100 m-auto">
        <div class="card">
            <div class="card-header bg-primary text-white text-center py-4">
                <h1 class="h3 mb-0"><i class="bi bi-cloud-fill me-2"></i>CloudStorage</h1>
                <p class="mb-0">Stockage sécurisé pour vos fichiers</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <h2 class="card-title text-center mb-4">Connexion</h2>
                
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
                    
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" placeholder="Mot de passe" required>
                        <label for="password"><i class="bi bi-lock me-1"></i>Mot de passe</label>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me">
                        <label class="form-check-label" for="remember_me">
                            Se souvenir de moi
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="forgot_password.php" class="text-decoration-none">Mot de passe oublié ?</a>
                    </div>
                </form>
            </div>
            
            <div class="card-footer bg-light py-3 text-center">
                <p class="mb-0">Pas encore de compte ? <a href="register.php" class="fw-bold text-decoration-none">S'inscrire</a></p>
            </div>
        </div>
        
        <div class="text-center mt-3 text-muted">
            <small>© <?= date('Y') ?> CloudStorage</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>