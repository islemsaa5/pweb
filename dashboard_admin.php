<?php
require_once 'config.php';
requireLogin();

// Seulement l'admin a accès
if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$page_title = 'Tableau de bord Admin';

// 1. Récupérer les statistiques de base
$nb_etudiants = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$nb_enseignants = $pdo->query("SELECT COUNT(*) FROM enseignants")->fetchColumn();
$nb_modules = $pdo->query("SELECT COUNT(*) FROM modules")->fetchColumn();

// 2. Calculer le taux de réussite (Étudiants ayant une moyenne >= 10)
$query_reussite = "
    SELECT COUNT(*) FROM (
        SELECT etudiant_id, AVG(note) as moyenne 
        FROM notes 
        GROUP BY etudiant_id 
        HAVING moyenne >= 10
    ) as reussite
";
$nb_reussite = $pdo->query($query_reussite)->fetchColumn();
$taux_reussite = $nb_etudiants > 0 ? round(($nb_reussite / $nb_etudiants) * 100, 1) : 0;

// 3. Récupérer les 5 derniers étudiants inscrits avec leur moyenne actuelle
$query_derniers = "
    SELECT e.*, 
    (SELECT AVG(note) FROM notes WHERE etudiant_id = e.id) as moyenne 
    FROM etudiants e 
    ORDER BY e.id DESC 
    LIMIT 5
";
$derniers_etudiants = $pdo->query($query_derniers)->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Dashboard Administrateur</h1>
        <p>Aperçu global de la scolarité - USTHB</p>
    </div>

    <!-- Cartes statistiques avec icônes (via emojis pour la simplicité) -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="number"><?= $nb_etudiants ?></div>
            <div class="label">👨‍🎓 Étudiants</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $nb_enseignants ?></div>
            <div class="label">👨‍🏫 Enseignants</div>
        </div>
        <div class="stat-card">
            <div class="number"><?= $nb_modules ?></div>
            <div class="label">📚 Modules</div>
        </div>
        <div class="stat-card success-card" style="border-bottom: 4px solid #28a745;">
            <div class="number" style="color:#28a745;"><?= $taux_reussite ?>%</div>
            <div class="label">📈 Taux de Réussite</div>
        </div>
    </div>

    <div class="notes-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
        
        <!-- Liste des derniers inscrits -->
        <div class="table-container">
            <div class="table-header">
                <h3>Dernières inscriptions</h3>
                <a href="etudiants.php" class="btn-action">Voir tous</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom & Prénom</th>
                        <th>Niveau</th>
                        <th>Moyenne</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($derniers_etudiants) > 0): ?>
                        <?php foreach ($derniers_etudiants as $etud): ?>
                            <?php 
                                $moy = $etud['moyenne'] !== null ? round($etud['moyenne'], 2) : '-';
                                $status_class = ($etud['moyenne'] >= 10) ? 'badge-admis' : ($etud['moyenne'] === null ? '' : 'badge-ajourne');
                                $status_text = ($etud['moyenne'] >= 10) ? 'Admis' : ($etud['moyenne'] === null ? 'N/A' : 'Ajourné');
                            ?>
                            <tr>
                                <td><span class="badge-code"><?= htmlspecialchars($etud['matricule']) ?></span></td>
                                <td><strong><?= htmlspecialchars($etud['nom'] . ' ' . $etud['prenom']) ?></strong></td>
                                <td><?= htmlspecialchars($etud['niveau']) ?></td>
                                <td><?= $moy ?> / 20</td>
                                <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="empty-row">Aucun étudiant enregistré.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Actions Rapides -->
        <div class="table-container">
            <div class="table-header">
                <h3>Actions Rapides</h3>
            </div>
            <div style="padding: 20px; display: flex; flex-direction: column; gap: 12px;">
                <a href="etudiants.php" class="btn-add" style="text-align:center;">➕ Gérer Étudiants</a>
                <a href="enseignants.php" class="btn-add" style="background:#5bc0de; text-align:center;">👥 Gérer Enseignants</a>
                <a href="modules.php" class="btn-add" style="background:#2c3e80; text-align:center;">📖 Gérer Modules</a>
                <a href="notes.php" class="btn-add" style="background:#f0ad4e; text-align:center;">📝 Saisie de Notes</a>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
