<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Mes Modules';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM modules WHERE enseignant_id = ? ORDER BY intitule");
$stmt->execute([$user_id]);
$modules = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Mes Modules</h1>
        <p>Les modules qui vous sont affectes</p>
    </div>

    <div class="modules-grid">
        <?php foreach ($modules as $m): ?>
        <div class="module-card" style="border-top: 3px solid #2c3e80;">
            <h4><?= htmlspecialchars($m['intitule']) ?></h4>
            <p style="font-weight: bold; margin-bottom: 10px;">
                Code : <span class="badge badge-code"><?= htmlspecialchars($m['code_module']) ?></span>
            </p>
            <p>Coefficient : <?= $m['coefficient'] ?></p>
            
            <div class="actions" style="margin-top: 15px;">
                <a href="saisie_notes.php" class="btn-add" style="display: block; text-align: center;">Saisir les notes</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if(empty($modules)): ?>
        <p class="empty-row" style="background: white; border: 1px solid #ddd; padding: 20px;">
            Aucun module ne vous a ete affecte pour le moment.
        </p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
