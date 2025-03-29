<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $originalName = $file['name'];
    // Chemin absolu vers le dossier uploads/ à la racine du projet
    $uploadDir = '/var/www/html/uploads/';
    $encryptedPath = $uploadDir . uniqid() . '.enc';

    // Simule un chiffrement (ici, on déplace simplement le fichier)
    if (move_uploaded_file($file['tmp_name'], $encryptedPath)) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, encrypted_path) VALUES (?, ?, ?)");
        // Stocke le chemin relatif dans la base pour download.php
        $relativePath = 'uploads/' . basename($encryptedPath);
        $stmt->execute([$_SESSION['user_id'], $originalName, $relativePath]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Erreur lors de l'upload.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Uploader un fichier</title>
</head>
<body>
    <h1>Uploader un fichier</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Uploader</button>
    </form>
    <a href="index.php">Retour</a>
</body>
</html>