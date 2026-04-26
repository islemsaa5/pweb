<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'etudiant') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Traitement de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo_profil'])) {
    $file = $_FILES['photo_profil'];
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions)) {
        $error = "Seuls les fichiers JPG, JPEG et PNG sont autorisés.";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = "La taille de l'image ne doit pas dépasser 2 Mo.";
    } else {
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $upload_path = "uploads/profiles/" . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            // Mettre à jour la base de données
            $stmt = $pdo->prepare("UPDATE etudiants SET photo = ? WHERE id = ?");
            $stmt->execute([$new_filename, $user_id]);
            $message = "Photo de profil mise à jour avec succès !";
        } else {
            $error = "Erreur lors de l'upload du fichier.";
        }
    }
}

// Récupérer les infos de l'étudiant
$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$user_id]);
$etudiant = $stmt->fetch();

$photo_path = (!empty($etudiant['photo']) && file_exists("uploads/profiles/" . $etudiant['photo'])) 
    ? "uploads/profiles/" . $etudiant['photo'] 
    : "assets/img/default-profile.png";

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Mon Profil</h1>
        <p>Gérez vos informations personnelles et votre photo</p>
    </div>

    <?php if ($message): ?><div class="msg-success animate-pop"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="msg-error animate-pop"><?= $error ?></div><?php endif; ?>

    <div class="profile-container glass-effect" style="max-width: 600px; margin: 0 auto; padding: 40px; border-radius: 20px; text-align: center;">
        
        <div class="profile-image-wrapper" style="position: relative; display: inline-block; margin-bottom: 25px;">
            <img src="<?= $photo_path ?>" id="preview" style="width: 180px; height: 180px; border-radius: 50%; object-fit: cover; border: 5px solid #2c3e80; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
            <button onclick="document.getElementById('fileInput').click()" style="position: absolute; bottom: 5px; right: 5px; background: #2c3e80; color: white; border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                <i class="fa-solid fa-camera"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="photoForm">
            <input type="file" name="photo_profil" id="fileInput" style="display: none;" onchange="this.form.submit()">
        </form>

        <h2 style="color: #2c3e80; margin-bottom: 5px;"><?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h2>
        <p style="color: #666; margin-bottom: 30px;"><?= htmlspecialchars($etudiant['matricule']) ?> | <?= htmlspecialchars($etudiant['niveau']) ?></p>

        <div class="profile-details" style="text-align: left; background: rgba(255,255,255,0.5); padding: 20px; border-radius: 15px;">
            <p style="margin-bottom: 10px;"><i class="fa-solid fa-envelope" style="width: 20px; color: #2c3e80;"></i> <strong>Email :</strong> <?= htmlspecialchars($etudiant['email']) ?></p>
            <p style="margin-bottom: 10px;"><i class="fa-solid fa-graduation-cap" style="width: 20px; color: #2c3e80;"></i> <strong>Section :</strong> <?= htmlspecialchars($etudiant['section']) ?></p>
            <p><i class="fa-solid fa-calendar" style="width: 20px; color: #2c3e80;"></i> <strong>Date de naissance :</strong> <?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : 'N/A' ?></p>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="dashboard_etudiant.php" class="btn-add" style="background: #2c3e80;">Retour au Dashboard</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
