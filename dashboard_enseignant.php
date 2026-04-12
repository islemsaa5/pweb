<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Tableau de bord Enseignant';
$user_id = $_SESSION['user_id'];

// Stats de l'enseignant
$modules = $pdo->prepare("SELECT * FROM modules WHERE enseignant_id = ?");
$modules->execute([$user_id]);
$mes_modules = $modules->fetchAll();
$nb_modules = count($mes_modules);

// Calculer le nombre d'etudiants notes
$nb_notes = 0;
if ($nb_modules > 0) {
    $mod_ids = implode(',', array_column($mes_modules, 'id'));
    $stmt = $pdo->query("SELECT COUNT(*) FROM notes WHERE module_id IN ($mod_ids)");
    $nb_notes = $stmt->fetchColumn();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Espace Enseignant</h1>
        <p>Bienvenue, M/Mme <?= htmlspecialchars($_SESSION['nom']) ?></p>
    </div>

    <!-- Cartes statistiques -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="number"><?= $nb_modules ?></div>
            <div class="label">Modules affectes</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $nb_notes ?></div>
            <div class="label">Notes saisies</div>
        </div>
    </div>

    <div class="form-row">
        <!-- Raccourcis -->
        <div class="table-container">
            <div class="table-header">
                <h3>Que souhaitez-vous faire ?</h3>
            </div>
            <div style="padding: 20px;">
                <a href="mes_modules.php" class="btn-add" style="display: block; margin-bottom: 10px; text-align: center;">Voir mes modules</a>
                <a href="saisie_notes.php" class="btn-add" style="display: block; margin-bottom: 10px; text-align: center; background-color: #5cb85c;">Saisir des notes</a>
                <a href="liste_etudiants.php" class="btn-add" style="display: block; text-align: center; background-color: #5bc0de;">Voir la liste des etudiants</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
