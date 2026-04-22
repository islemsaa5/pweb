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

if ($_SESSION['role'] !== 'etudiant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Tableau de bord Étudiant';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$user_id]);
$etudiant = $stmt->fetch();

$query = "
    SELECT n.note, m.intitule, m.coefficient, m.semestre, m.code_module
    FROM notes n 
    JOIN modules m ON n.module_id = m.id 
    WHERE n.etudiant_id = ?
    ORDER BY m.semestre ASC, m.intitule ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$all_notes = $stmt->fetchAll();

$semestres = [1 => [], 2 => []];
$stats = [
    1 => ['total_points' => 0, 'total_coeffs' => 0],
    2 => ['total_points' => 0, 'total_coeffs' => 0]
];

foreach ($all_notes as $n) {
    $semestres[$n['semestre']][] = $n;
    $stats[$n['semestre']]['total_points'] += ($n['note'] * $n['coefficient']);
    $stats[$n['semestre']]['total_coeffs'] += $n['coefficient'];
}

$moy_s1 = $stats[1]['total_coeffs'] > 0 ? $stats[1]['total_points'] / $stats[1]['total_coeffs'] : null;
$moy_s2 = $stats[2]['total_coeffs'] > 0 ? $stats[2]['total_points'] / $stats[2]['total_coeffs'] : null;

$moy_annuelle = null;
if ($moy_s1 !== null && $moy_s2 !== null) {
    $moy_annuelle = ($moy_s1 + $moy_s2) / 2;
} elseif ($moy_s1 !== null) {
    $moy_annuelle = $moy_s1;
} elseif ($moy_s2 !== null) {
    $moy_annuelle = $moy_s2;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Mon Espace Étudiant</h1>
        <p>Suivi de Scolarité - <?= htmlspecialchars($etudiant['niveau']) ?></p>
    </div>

    <!-- Résumé Annuel -->
    <div class="profile-card glass-effect" style="margin-bottom: 30px;">
        <div class="info">
            <h2 style="color: var(--primary-color); margin-bottom: 15px;">Moyenne Annuelle</h2>
            <p><strong>Année Universitaire :</strong> 2025/2026</p>
            <p><strong>Niveau :</strong> <?= htmlspecialchars($etudiant['niveau']) ?></p>
        </div>
        
        <div class="moyenne-box" style="background: white; border-color: var(--primary-color);">
            <?php if ($moy_annuelle !== null): ?>
                <div class="value" style="font-size: 32px; color: var(--primary-color);"><?= number_format($moy_annuelle, 2) ?></div>
                <div class="label">Moyenne Générale</div>
                <div style="margin-top: 10px;">
                    <span class="badge <?= $moy_annuelle >= 10 ? 'badge-admis' : 'badge-ajourne' ?>">
                        <?= $moy_annuelle >= 10 ? 'ADMIS' : 'AJOURNÉ' ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="value">--/20</div>
                <div class="label">En attente de notes</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="notes-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
        
        <!-- SEMESTRE 1 -->
        <div class="semestre-section">
            <div class="table-container">
                <div class="table-header" style="background: #f8fafc;">
                    <h3>Semestre 1</h3>
                    <span class="badge" style="background: #2c3e80; color: white;">
                        Moy: <?= $moy_s1 !== null ? number_format($moy_s1, 2) . '/20' : '--' ?>
                    </span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Coeff</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($semestres[1])): ?>
                            <?php foreach ($semestres[1] as $n): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($n['intitule']) ?></strong></td>
                                    <td><?= $n['coefficient'] ?></td>
                                    <td style="color: <?= $n['note'] >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>; font-weight: 600;">
                                        <?= number_format($n['note'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="empty-row">Aucune note pour le S1</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SEMESTRE 2 -->
        <div class="semestre-section">
            <div class="table-container">
                <div class="table-header" style="background: #f8fafc;">
                    <h3>Semestre 2</h3>
                    <span class="badge" style="background: #2c3e80; color: white;">
                        Moy: <?= $moy_s2 !== null ? number_format($moy_s2, 2) . '/20' : '--' ?>
                    </span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Coeff</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($semestres[2])): ?>
                            <?php foreach ($semestres[2] as $n): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($n['intitule']) ?></strong></td>
                                    <td><?= $n['coefficient'] ?></td>
                                    <td style="color: <?= $n['note'] >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>; font-weight: 600;">
                                        <?= number_format($n['note'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="empty-row">Aucuna note pour le S2</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="margin-top: 25px; display: flex; gap: 15px;">
        <a href="releve_notes.php" class="btn-add" style="flex: 1; text-align: center; background: #2c3e80;"><i class="fa-solid fa-file-invoice"></i> Télécharger le Relevé Annuel</a>
        <a href="mes_modules.php" class="btn-add" style="flex: 1; text-align: center; background: #5bc0de;"><i class="fa-solid fa-book"></i> Voir le Programme de l'Année</a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
