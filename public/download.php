<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if ($file) {
        // Chemin absolu pour lire le fichier
        $filePath = '/var/www/html/' . $file['encrypted_path'];
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            readfile($filePath);
            exit;
        } else {
            die("Fichier non trouvé sur le serveur.");
        }
    } else {
        die("Fichier non trouvé dans la base de données.");
    }
}
?>