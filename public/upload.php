<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $key = 'ma_cle_secrete_32_caracteres!'; // Clé de 32 caractères pour AES-256
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt(file_get_contents($file['tmp_name']), 'aes-256-cbc', $key, 0, $iv);

    // Chemin pour stocker le fichier
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $filename = uniqid() . '.enc';
    $filePath = $uploadDir . $filename;

    // Écrit le fichier chiffré
    if (file_put_contents($filePath, $iv . $encrypted) !== false) {
        $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, encrypted_path) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $file['name'], $filename]);
        header("Location: index.php");
        exit;
    } else {
        $error = "Erreur lors de l'upload : impossible d'écrire le fichier.";
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