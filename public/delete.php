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
        // Chemin du fichier sur le disque
        $filePath = '../uploads/' . $file['encrypted_path'];

        // Supprime le fichier du disque s'il existe
        if (file_exists($filePath)) {
            unlink($filePath);
        }

      
        $deleteStmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $deleteStmt->execute([$_GET['id'], $_SESSION['user_id']]);


        header("Location: index.php?success=Fichier supprimé avec succès.");
        exit;
    } else {
        $error = "Fichier non trouvé ou accès refusé.";
    }
} else {
    $error = "ID de fichier non spécifié.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Erreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <a href="index.php" class="btn btn-primary">Retour</a>
    </div>
</body>
</html>
