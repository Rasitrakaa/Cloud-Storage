<?php
session_start();
require '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->execute([$email, $password]);
    header("Location: login.php");
    exit;
}
?>
<form method="POST">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <button type="submit">S’inscrire</button>
</form>