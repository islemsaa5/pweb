<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Rebuilt Premium Login Page
 */
require_once 'config.php';

// Auto-redirect if already logged in
if (isLoggedIn()) {
    $redirects = [
        'admin'      => 'dashboard_admin.php',
        'enseignant' => 'dashboard_enseignant.php',
        'etudiant'   => 'dashboard_etudiant.php'
    ];
    header('Location: ' . ($redirects[$_SESSION['role']] ?? 'index.php'));
    exit;
}

$error = '';
$identifiant = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = clean($_POST['identifiant'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifiant) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $user = null;
        $role = '';

        // 1. Check Admin
        $stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE email = ?");
        $stmt->execute([$identifiant]);
        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = $res;
            $role = 'admin';
        }

        // 2. Check Teacher (if not admin)
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM enseignants WHERE matricule = ? OR email = ?");
            $stmt->execute([$identifiant, $identifiant]);
            if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = $res;
                $role = 'enseignant';
            }
        }

        // 3. Check Student (if not teacher)
        if (!$user) {
            $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE matricule = ? OR email = ?");
            $stmt->execute([$identifiant, $identifiant]);
            if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $user = $res;
                $role = 'etudiant';
            }
        }

        // Verify Password
        if ($user) {
            if (password_verify($password, $user['mot_de_passe'])) {
                // Success! Populate Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $role;
                $_SESSION['nom']     = $user['nom'];
                $_SESSION['prenom']  = $user['prenom'];
                $_SESSION['email']   = $user['email'] ?? '';
                $_SESSION['matricule'] = $user['matricule'] ?? '';

                header('Location: ' . ($role === 'admin' ? 'dashboard_admin.php' : ($role === 'enseignant' ? 'dashboard_enseignant.php' : 'dashboard_etudiant.php')));
                exit;
            } else {
                $error = 'Mot de passe incorrect.';
            }
        } else {
            $error = 'Identifiant inconnu.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTHB - Portail de Scolarité</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e80;
            --accent: #4f46e5;
            --bg-dark: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, hsla(225, 39%, 20%, 1) 0, transparent 50%), 
                radial-gradient(at 100% 100%, hsla(232, 47%, 18%, 1) 0, transparent 50%);
            color: #fff;
            overflow: hidden;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 40px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            width: 80px;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 15px rgba(79, 70, 229, 0.4));
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: #cbd5e1;
            margin-left: 4px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 16px;
            transition: color 0.3s;
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 48px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }

        input:focus + i {
            color: var(--accent);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--accent);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .btn-login:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .footer-info {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
                border-radius: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="header">
            <img src="assets/img/logo.png" alt="USTHB Logo" class="logo">
            <h1>Scolarité USTHB</h1>
            <p>Portail d'accès sécurisé</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="identifiant">Matricule ou Email</label>
                <div class="input-wrapper">
                    <input type="text" id="identifiant" name="identifiant" 
                           placeholder="Ex: 242431XXXXXX" 
                           value="<?= htmlspecialchars($identifiant) ?>" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" 
                           placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Se connecter <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>

        <div class="footer-info">
            &copy; 2026 USTHB - Faculté d'Informatique<br>
            <small>Système de gestion académique</small>
        </div>
    </div>

</body>
</html>
