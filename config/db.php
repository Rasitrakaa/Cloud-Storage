<?php
$dbUrl = getenv("DATABASE_URL");
if ($dbUrl === false || empty($dbUrl)) {
    die("Erreur : La variable d’environnement DATABASE_URL n’est pas définie.");
}

$dbUrl = parse_url($dbUrl);
if ($dbUrl === false) {
    die("Erreur : Impossible de parser DATABASE_URL.");
}

$host = $dbUrl["host"] ?? die("Erreur : Hôte non trouvé dans DATABASE_URL.");
$dbname = isset($dbUrl["path"]) ? substr($dbUrl["path"], 1) : die("Erreur : Nom de la base non trouvé dans DATABASE_URL.");
$user = $dbUrl["user"] ?? die("Erreur : Utilisateur non trouvé dans DATABASE_URL.");
$pass = $dbUrl["pass"] ?? die("Erreur : Mot de passe non trouvé dans DATABASE_URL.");

// Utilise pgsql au lieu de mysql pour PostgreSQL
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>