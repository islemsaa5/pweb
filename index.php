<?php
require_once 'config.php';

// Si déjà connecté, rediriger vers le tableau de bord
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
$active_tab = 'login'; // 'login' ou 'register'

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $active_tab = 'login';
        $identifiant = clean($_POST['identifiant'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($identifiant) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            // 1. Chercher dans administrateurs (par email)
            $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = ?");
            $stmt->execute([$identifiant]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $role = 'admin';

            // 2. Chercher dans enseignants (par matricule ou email)
            if (!$user) {
                $stmt = $pdo->prepare("SELECT * FROM enseignants WHERE matricule = ? OR email = ?");
                $stmt->execute([$identifiant, $identifiant]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $role = 'enseignant';
            }

            // 3. Chercher dans etudiants (par matricule ou email)
            if (!$user) {
                $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE matricule = ? OR email = ?");
                $stmt->execute([$identifiant, $identifiant]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $role = 'etudiant';
            }

            // Vérifier le mot de passe
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
        }
    } elseif ($action === 'register') {
        $active_tab = 'register';
        $matricule = clean($_POST['matricule'] ?? '');
        $nom = clean($_POST['nom'] ?? '');
        $prenom = clean($_POST['prenom'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $date_naissance = clean($_POST['date_naissance'] ?? '');

        if (empty($matricule) || empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password) || empty($date_naissance)) {
            $error = 'Veuillez remplir tous les champs du formulaire d\'inscription.';
        } elseif ($password !== $confirm_password) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            // Vérifier existance
            $stmt = $pdo->prepare("SELECT id FROM etudiants WHERE matricule = ? OR email = ?");
            $stmt->execute([$matricule, $email]);
            if ($stmt->fetch()) {
                $error = 'Ce matricule ou cet email est déjà utilisé.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO etudiants (matricule, nom, prenom, date_naissance, email, mot_de_passe) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$matricule, $nom, $prenom, $date_naissance, $email, $hash])) {
                    $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                    $active_tab = 'login';
                } else {
                    $error = 'Erreur lors de l\'inscription.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Connexion & Inscription</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">
</head>
<body class="auth-body">

<div class="auth-container glass-effect">
    
    <!-- Logo Officiel USTHB -->
    <div class="auth-logo">
        <img src="https://upload.wikimedia.org/wikipedia/fr/5/52/USTHB_Logo.png" alt="USTHB Logo" class="official-logo-main">
        <h1>Scolarité USTHB</h1>
        <p>Faculté d'Informatique</p>
    </div>

    <!-- Toggle Buttons -->
    <div class="auth-toggle">
        <button type="button" class="toggle-btn <?= $active_tab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Connexion</button>
        <button type="button" class="toggle-btn <?= $active_tab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Inscription</button>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="msg-success animate-pop"><?= $success ?></div>
    <?php endif; ?>

    <!-- Formulaire de Connexion -->
    <form method="POST" id="form-login" class="auth-form <?= $active_tab === 'login' ? 'active-form' : '' ?>">
        <input type="hidden" name="action" value="login">
        
        <div class="input-group">
            <label for="identifiant">Matricule ou Email</label>
            <input type="text" id="identifiant" name="identifiant" placeholder="Ex: 20230001 ou admin@usthb.dz" required>
        </div>

        <div class="input-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="auth-submit-btn">Se connecter</button>
    </form>

    <!-- Formulaire d'Inscription (Étudiant) -->
    <form method="POST" id="form-register" class="auth-form <?= $active_tab === 'register' ? 'active-form' : '' ?>">
        <input type="hidden" name="action" value="register">
        
        <div class="input-row">
            <div class="input-group">
                <label for="reg_matricule">Matricule</label>
                <input type="text" id="reg_matricule" name="matricule" placeholder="Ex: 20240001" required>
            </div>
            <div class="input-group">
                <label for="reg_date_naissance">Date de naissance</label>
                <input type="date" id="reg_date_naissance" name="date_naissance" required>
            </div>
        </div>

        <div class="input-row">
            <div class="input-group">
                <label for="reg_nom">Nom</label>
                <input type="text" id="reg_nom" name="nom" placeholder="Votre nom" required>
            </div>
            <div class="input-group">
                <label for="reg_prenom">Prénom</label>
                <input type="text" id="reg_prenom" name="prenom" placeholder="Votre prénom" required>
            </div>
        </div>

        <div class="input-group">
            <label for="reg_email">Email Universitaire</label>
            <input type="email" id="reg_email" name="email" placeholder="nom.prenom@etud.usthb.dz" required>
        </div>

        <div class="input-row">
            <div class="input-group">
                <label for="reg_password">Mot de passe</label>
                <input type="password" id="reg_password" name="password" required>
            </div>
            <div class="input-group">
                <label for="reg_confirm_password">Confirmer</label>
                <input type="password" id="reg_confirm_password" name="confirm_password" required>
            </div>
        </div>

        <button type="submit" class="auth-submit-btn">S'inscrire</button>
    </form>
</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.toggle-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active-form'));
        
        if (tab === 'login') {
            document.querySelector('.toggle-btn:nth-child(1)').classList.add('active');
            document.getElementById('form-login').classList.add('active-form');
        } else {
            document.querySelector('.toggle-btn:nth-child(2)').classList.add('active');
            document.getElementById('form-register').classList.add('active-form');
        }
    }
</script>

</body>
</html>
