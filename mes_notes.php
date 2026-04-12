<?php
require_once 'config.php';
requireLogin();

if ($_SESSION['role'] !== 'etudiant') {
    header('Location: index.php');
    exit;
}

$page_title = 'Mes Notes';
$user_id = $_SESSION['user_id'];

// Recuperer les notes detaillees
$stmt = $pdo->prepare("
    SELECT m.code_module, m.intitule, m.coefficient, n.note,
           e.nom as ens_nom, e.prenom as ens_prenom
    FROM modules m
    LEFT JOIN notes n ON m.id = n.module_id AND n.etudiant_id = ?
    LEFT JOIN enseignants e ON m.enseignant_id = e.id
    ORDER BY m.intitule
");
$stmt->execute([$user_id]);
$notes = $stmt->fetchAll();

$total_c = 0;
$total_n = 0;

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Mes Notes</h1>
        <p>Apercu de vos resultats par module</p>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Module</th>
                    <th>Code</th>
                    <th>Enseignant</th>
                    <th>Coefficient</th>
                    <th>Note</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['intitule']) ?></td>
                    <td><span class="badge badge-code"><?= htmlspecialchars($n['code_module']) ?></span></td>
                    <td>
                        <?= $n['ens_nom'] ? htmlspecialchars($n['ens_prenom'].' '.$n['ens_nom']) : '...' ?>
                    </td>
                    <td style="text-align: center;"><?= $n['coefficient'] ?></td>
                    
                    <?php if ($n['note'] !== null): ?>
                        <?php 
                        $total_c += $n['coefficient'];
                        $total_n += $n['note'] * $n['coefficient'];
                        ?>
                        <td><strong><?= number_format($n['note'], 2) ?></strong></td>
                        <td>
                            <span class="badge <?= $n['note'] >= 10 ? 'badge-admis' : 'badge-ajourne' ?>">
                                <?= $n['note'] >= 10 ? 'Valide' : 'Non valide' ?>
                            </span>
                        </td>
                    <?php else: ?>
                        <td style="color: #999;">Pas encore note</td>
                        <td>-</td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            
            <?php if ($total_c > 0): ?>
            <tfoot style="background-color: #f0f2f5;">
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">Moyenne Generale :</td>
                    <td style="text-align: center; font-weight: bold;"><?= $total_c ?> (Total Coeff)</td>
                    <td colspan="2" style="font-size: 16px; font-weight: bold;">
                        <?php 
                        $moyenne = $total_n / $total_c;
                        $color = $moyenne >= 10 ? 'color: green;' : 'color: red;';
                        ?>
                        <span style="<?= $color ?>"><?= number_format($moyenne, 2) ?> / 20</span>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
