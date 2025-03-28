<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$file_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $_SESSION['user_id']]);
$file = $stmt->fetch();
if ($file) {
    $key = 'ma_cle_secrete_32_caracteres!';
    $data = file_get_contents("../uploads/" . $file['encrypted_path']);
    $iv = substr($data, 0, 16); // Récupère l'IV
    $encrypted = substr($data, 16);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    echo $decrypted;
    exit;
}
echo "Fichier non trouvé ou accès refusé.";
?>