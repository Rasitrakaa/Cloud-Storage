<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'connexion.php';

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

// Modifier le mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        $stmt = $conn->prepare("SELECT password FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch();

        if (password_verify($oldPassword, $userData['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashedPassword, $user_id]);
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
    <title>Profil</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Accueil</a></li>
            <li><a href="profile.php">Profil</a></li>
            <li><a href="logout.php">Déconnexion</a></li>
        </ul>
    </nav>
    
    <div class="profile-container">
        <h2>Bienvenue, <?php echo htmlspecialchars($user['nom']); ?>!</h2>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Téléphone: <?php echo htmlspecialchars($user['telephone']); ?></p>
        <a href="edit_profile.php">Modifier le profil</a>
    </div>
    
    <div class="password-update-container">
        <h3>Changer de mot de passe</h3>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success) ?> </div>
        <?php endif; ?>
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
