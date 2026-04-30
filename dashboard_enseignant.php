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
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Tableau de bord Enseignant';
$user_id = $_SESSION['user_id'];

$modules = $pdo->prepare("SELECT * FROM modules WHERE enseignant_id = ?");
$modules->execute([$user_id]);
$mes_modules = $modules->fetchAll();
$nb_modules = count($mes_modules);

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
                <a href="mes_modules.php" class="btn-add" style="display: block; margin-bottom: 10px; text-align: center;"><i class="fa-solid fa-book"></i> Voir mes modules</a>
                <a href="saisie_notes.php" class="btn-add" style="display: block; margin-bottom: 10px; text-align: center;"><i class="fa-solid fa-pen-to-square"></i> Saisir des notes</a>
                <a href="liste_etudiants.php" class="btn-add" style="display: block; text-align: center;"><i class="fa-solid fa-users"></i> Voir la liste des etudiants</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
