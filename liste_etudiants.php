<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'enseignant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Liste des Etudiants';
$user_id = $_SESSION['user_id'];

// Recuperer d'abord les IDs des modules de cet enseignant
$mod_stmt = $pdo->prepare("SELECT id FROM modules WHERE enseignant_id = ?");
$mod_stmt->execute([$user_id]);
$modules_ids = $mod_stmt->fetchAll(PDO::FETCH_COLUMN);

$etudiants = [];
if (!empty($modules_ids)) {
    // Si l'enseignant a des modules, trouver les eleves inscrits (ceux qui ont des notes dans ces modules)
    $in_clause = implode(',', $modules_ids);
    $stmt = $pdo->query("
        SELECT DISTINCT e.* 
        FROM etudiants e
        JOIN notes n ON e.id = n.etudiant_id
        WHERE n.module_id IN ($in_clause)
        ORDER BY e.nom
    ");
    $etudiants = $stmt->fetchAll();
} else {
    // S'il n'a pas de modules, on peut au moins lui lister tous les eleves meme s'il n'en est pas responsable (choix pedagogique simplifie)
    $etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nom")->fetchAll();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Liste des Etudiants</h1>
        <p>Les etudiants concernes par vos modules (ou tous les etudiants)</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prenom</th>
                    <th>Niveau</th>
                    <th>Email</th>
                    <th>Date de Naissance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                <tr>
                    <td><span class="badge badge-code"><?= htmlspecialchars($e['matricule']) ?></span></td>
                    <td><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></td>
                    <td><?= htmlspecialchars($e['niveau']) ?></td>
                    <td><?= htmlspecialchars($e['email']) ?></td>
                    <td><?= $e['date_naissance'] ? date('d/m/Y', strtotime($e['date_naissance'])) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($etudiants)): ?>
                <tr><td colspan="5" class="empty-row">Aucun etudiant trouve.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
