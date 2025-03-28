<?php
session_start();
require '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$files = $stmt->fetchAll();
?>
<h1>Mes fichiers</h1>
<ul>
<?php foreach ($files as $file): ?>
    <li><a href="download.php?id=<?= $file['id'] ?>"><?= htmlspecialchars($file['original_name']) ?></a></li>
<?php endforeach; ?>
</ul>
<a href="upload.php">Uploader un fichier</a>