<?php
require_once 'config.php';

// Si deja connecte, rediriger vers le tableau de bord
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':       header('Location: dashboard_admin.php'); break;
        case 'enseignant':  header('Location: dashboard_enseignant.php'); break;
        case 'etudiant':    header('Location: dashboard_etudiant.php'); break;
    }
    exit;
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = clean($_POST['role'] ?? '');

    if (empty($email) || empty($password) || empty($role)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Chercher l'utilisateur selon le role
        $user = null;

        if ($role === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = ?");
        } elseif ($role === 'enseignant') {
            $stmt = $pdo->prepare("SELECT * FROM enseignants WHERE email = ?");
        } elseif ($role === 'etudiant') {
            $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE email = ?");
        }

        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifier le mot de passe
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Creer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $role;
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];

            // Rediriger vers le tableau de bord
            switch ($role) {
                case 'admin':      header('Location: dashboard_admin.php'); break;
                case 'enseignant': header('Location: dashboard_enseignant.php'); break;
                case 'etudiant':   header('Location: dashboard_etudiant.php'); break;
            }
            exit;
        } else {
            $error = 'Email, mot de passe ou role incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - USTHB Scolarite</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Barre de navigation -->
<div class="navbar">
    <div class="logo">USTHB <span>| Gestion de la Scolarite</span></div>
    <ul>
        <li><a href="index.php">Accueil</a></li>
        <li><a href="login.php" class="active">Connexion</a></li>
    </ul>
</div>

<!-- Formulaire de connexion -->
<div class="login-container">
    <h2>Connexion</h2>

    <?php if ($error): ?>
        <div class="msg-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <!-- Selection du role -->
        <div class="form-group">
            <label>Vous etes :</label>
            <div class="role-select">
                <label>
                    <input type="radio" name="role" value="admin" <?= (isset($role) && $role === 'admin') ? 'checked' : '' ?>>
                    <span>Administrateur</span>
                </label>
                <label>
                    <input type="radio" name="role" value="enseignant" <?= (isset($role) && $role === 'enseignant') ? 'checked' : '' ?>>
                    <span>Enseignant</span>
                </label>
                <label>
                    <input type="radio" name="role" value="etudiant" <?= (isset($role) && $role === 'etudiant') ? 'checked' : '' ?>>
                    <span>Etudiant</span>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="votre@email.dz"
                   value="<?= htmlspecialchars($email ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" placeholder="Mot de passe" required>
        </div>

        <button type="submit" class="btn-submit">Se connecter</button>
    </form>

    <p style="text-align:center; margin-top:15px; font-size:12px; color:#888;">
        <a href="index.php">Retour a l'accueil</a>
    </p>
</div>

<!-- Footer -->
<div class="footer">
    <p>USTHB - Faculte d'Informatique | PWEB 2025/2026</p>
</div>

</body>
</html>
