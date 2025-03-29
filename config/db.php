<?php
$host = 'localhost';
$dbname = 'file_sharing';
$user = 'root';
$pass = ''; // Remplace par ton mot de passe MySQL local
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>