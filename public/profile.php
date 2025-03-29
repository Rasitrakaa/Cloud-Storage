<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer les informations de l'utilisateur
$userStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

// Modifier le mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $userData = $stmt->fetch();

        if (password_verify($oldPassword, $userData['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $_SESSION['user_id']]);
            $success = "Mot de passe mis à jour avec succès.";
        } else {
            $error = "Ancien mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - CloudStorage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">Mon Profil</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>
        
        <div class="card p-4">
            <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <h3 class="mt-4">Changer de mot de passe</h3>
        <form method="post">
            <div class="mb-3">
                <label for="old_password" class="form-label">Ancien mot de passe</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
</body>
</html>
