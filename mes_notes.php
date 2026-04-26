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

$page_title = 'Mes Notes';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT m.code_module, m.intitule, m.coefficient, m.semestre, n.note,
           e.nom as ens_nom, e.prenom as ens_prenom
    FROM modules m
    LEFT JOIN notes n ON m.id = n.module_id AND n.etudiant_id = ?
    LEFT JOIN enseignants e ON m.enseignant_id = e.id
    ORDER BY m.semestre, m.intitule
");
$stmt->execute([$user_id]);
$all_notes = $stmt->fetchAll();

$semestres = [1 => [], 2 => []];
foreach ($all_notes as $n) {
    $semestres[$n['semestre']][] = $n;
}

$global_c = 0;
$global_n = 0;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Mes Notes</h1>
        <p>Aperçu de vos résultats par semestre</p>
    </div>

    <?php foreach ([1, 2] as $s): ?>
    <h3 style="margin: 25px 0 15px; color: var(--primary-color); border-left: 4px solid var(--primary-color); padding-left: 10px;">
        Semestre <?= $s ?>
    </h3>
    
    <div class="table-container" style="margin-bottom: 30px;">
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Enseignant</th>
                    <th style="text-align: center;">Coeff</th>
                    <th style="text-align: center;">Note</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $sem_c = 0;
                $sem_n = 0;
                if (!empty($semestres[$s])):
                    foreach ($semestres[$s] as $n): 
                        if ($n['note'] !== null) {
                            $sem_c += $n['coefficient'];
                            $sem_n += $n['note'] * $n['coefficient'];
                            $global_c += $n['coefficient'];
                            $global_n += $n['note'] * $n['coefficient'];
                        }
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($n['intitule']) ?></strong></td>
                    <td><span class="badge badge-code"><?= htmlspecialchars($n['code_module']) ?></span></td>
                    <td><?= $n['ens_nom'] ? htmlspecialchars($n['ens_prenom'].' '.$n['ens_nom']) : '<span style="color:#999;">...</span>' ?></td>
                    <td style="text-align: center;"><?= $n['coefficient'] ?></td>
                    <td style="text-align: center;">
                        <?= ($n['note'] !== null) ? '<strong>'.number_format($n['note'], 2).'</strong>' : '<span style="color:#999;">-</span>' ?>
                    </td>
                    <td>
                        <?php if ($n['note'] !== null): ?>
                            <span class="badge <?= $n['note'] >= 10 ? 'badge-admis' : 'badge-ajourne' ?>">
                                <?= $n['note'] >= 10 ? 'Valide' : 'Ajourné' ?>
                            </span>
                        <?php else: ?>
                            <span style="color:#999; font-style:italic;">En attente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="6" class="empty-row">Aucun module pour ce semestre</td></tr>
                <?php endif; ?>
            </tbody>
            <?php if ($sem_c > 0): ?>
            <tfoot style="background-color: #f8fafc;">
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">Moyenne Semestrielle :</td>
                    <td style="text-align: center; font-weight: bold;"><?= $sem_c ?></td>
                    <td colspan="2" style="font-weight: bold;">
                        <?php $moy_s = $sem_n / $sem_c; ?>
                        <span style="color: <?= $moy_s >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>; font-size: 15px;">
                            <?= number_format($moy_s, 2) ?> / 20
                        </span>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
    <?php endforeach; ?>

    <!-- Résumé Annuel -->
    <?php if ($global_c > 0): ?>
    <div class="glass-effect" style="padding: 20px; border-radius: 12px; background: white; margin-top: 20px; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 style="color: #64748b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 5px;">Moyenne Générale Annuelle</h4>
            <div style="font-size: 11px; color: #94a3b8;">Calculée sur <?= $global_c ?> coefficients</div>
        </div>
        <?php $moy_a = $global_n / $global_c; ?>
        <div style="font-size: 28px; font-weight: 800; color: <?= $moy_a >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>;">
            <?= number_format($moy_a, 2) ?> <span style="font-size: 16px; font-weight: 500; color: #94a3b8;">/ 20</span>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
