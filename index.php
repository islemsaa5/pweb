<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
require_once 'config.php';

if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':       header('Location: dashboard_admin.php'); break;
        case 'enseignant':  header('Location: dashboard_enseignant.php'); break;
        case 'etudiant':    header('Location: dashboard_etudiant.php'); break;
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = clean($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($identifiant) && !empty($password)) {

        $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = ?");
        $stmt->execute([$identifiant]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $role = 'admin';

        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM enseignants WHERE matricule = ? OR email = ?");
            $stmt->execute([$identifiant, $identifiant]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $role = 'enseignant';
        }

        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE matricule = ? OR email = ?");
            $stmt->execute([$identifiant, $identifiant]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $role = 'etudiant';
        }

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $role;
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];

            switch ($role) {
                case 'admin':      header('Location: dashboard_admin.php'); break;
                case 'enseignant': header('Location: dashboard_enseignant.php'); break;
                case 'etudiant':   header('Location: dashboard_etudiant.php'); break;
            }
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Connexion</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">
</head>
<body class="auth-body">

<div class="auth-container glass-effect">
    
    <!-- Logo Officiel USTHB -->
    <div class="auth-logo">
        <img src="assets/img/logo.png" alt="USTHB Logo" class="official-logo-main">
        <h1>Scolarité USTHB</h1>
        <p>Faculté d'Informatique</p>
    </div>

    <div class="auth-header-simple">
        <h2>Connexion au Portail</h2>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg-success animate-pop"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulaire de Connexion -->
    <form method="POST" id="form-login">
        
        <div class="input-group">
            <label for="identifiant">Matricule ou Email</label>
            <input type="text" id="identifiant" name="identifiant" placeholder="Ex: 20230001 ou admin@usthb.dz" required>
        </div>

        <div class="input-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢" required>
        </div>

        <button type="submit" class="auth-submit-btn">Se connecter</button>
    </form>
</div>

</body>
</html>
