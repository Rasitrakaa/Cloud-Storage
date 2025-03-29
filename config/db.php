<?php
$dbUrl = parse_url(getenv("DATABASE_URL"));
$host = $dbUrl["host"];
$dbname = substr($dbUrl["path"], 1);
$user = $dbUrl["user"];
$pass = $dbUrl["pass"];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>