<?php
// ============================================
// Fichier de configuration
// Connexion a la base de donnees MySQL
// ============================================

$host = '127.0.0.1';
$port= 3007;
$dbname = 'gestion_scolarite';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Demarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonction pour nettoyer les donnees
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Verifier si l'utilisateur est connecte
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Rediriger si non connecte
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
?>
