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
        $filePath = '../uploads/' . $file['encrypted_path'];
        if (file_exists($filePath)) {
            $key = 'ma_cle_secrete_32_caracteres!';
            $data = file_get_contents($filePath);
            $iv = substr($data, 0, 16); // Récupère l'IV
            $encrypted = substr($data, 16);
            $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
            if ($decrypted === false) {
                die("Erreur lors du déchiffrement.");
            }
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
            echo $decrypted;
            exit;
        } else {
            die("Fichier non trouvé sur le serveur : " . $filePath);
        }
    } else {
        die("Fichier non trouvé dans la base de données.");
    }
}
die("ID de fichier non spécifié.");
?>