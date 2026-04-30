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

$user_id = $_GET['id'] ?? (($_SESSION['role'] === 'etudiant') ? $_SESSION['user_id'] : null);

if (!$user_id) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['role'] !== 'admin' && $user_id != $_SESSION['user_id']) {
    $user_id = $_SESSION['user_id'];
}

$stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$user_id]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    die("Étudiant non trouvé.");
}

$query = "
    SELECT m.code_module, m.intitule, m.coefficient, m.credits, m.semestre, n.note
    FROM modules m 
    LEFT JOIN notes n ON m.id = n.module_id AND n.etudiant_id = ?
    ORDER BY m.semestre ASC, m.intitule ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$all_notes = $stmt->fetchAll();

$semestres = [1 => [], 2 => []];
foreach ($all_notes as $n) {
    $semestres[$n['semestre']][] = $n;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé de Notes - USTHB - <?= htmlspecialchars($etudiant['nom']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: 'Arial', sans-serif; background: #f4f7f6; padding: 20px; color: #000; margin: 0; }
        .releve-paper { background: white; width: 277mm; min-height: 190mm; margin: 0 auto; padding: 15mm; box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative; box-sizing: border-box; }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px; }
        .header-left, .header-right { width: 40%; font-size: 10px; font-weight: bold; line-height: 1.3; }
        .header-right { text-align: right; }
        .header-center { width: 20%; text-align: center; }
        .header-center img { width: 50px; margin-bottom: 5px; }
        .duplicata { border: 2px solid #000; padding: 3px 20px; font-size: 16px; font-weight: bold; display: inline-block; letter-spacing: 2px; }
        
        .info-section { display: flex; flex-wrap: wrap; margin-bottom: 5px; font-size: 11px; font-weight: bold; }
        .info-col { width: 50%; }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { width: 130px; }
        .info-value { flex: 1; }
        
        .title-center { text-align: center; font-size: 18px; font-weight: bold; letter-spacing: 1px; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; border: 1px solid #000; }
        th, td { border: 1px solid #000; padding: 3px 4px; text-align: center; font-size: 10px; }
        th { background-color: #e8e8e8; font-weight: bold; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        
        .semester-row td { background-color: #f0f0f0; font-weight: bold; font-size: 11px; padding: 5px; }
        .footer-stats { display: flex; justify-content: space-between; font-size: 11px; font-weight: bold; margin-top: 5px; }
        
        .btn-print, .btn-back { 
            padding: 8px 15px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; text-decoration: none; margin-bottom: 15px;
        }
        .btn-print { background: #000; color: white; margin-left: 10px; }
        .btn-back { background: #6c757d; color: white; }

        @media print {
            body { background: white; padding: 0; }
            .releve-paper { box-shadow: none; margin: 0; width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width: 277mm; margin: 0 auto; display: flex; justify-content: space-between;">
    <a href="dashboard_etudiant.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour au Dashboard</a>
    <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimer le Relevé</button>
</div>

<div class="releve-paper">
    <div class="header">
        <div class="header-left">
            Ministère de l'Enseignement Supérieur et de la Recherche Scientifique<br>
            université des sciences et de la technologie houari boumediène alger<br>
            Faculté d'Informatique<br>
            Département des Systèmes Informatiques
        </div>
        <div class="header-center">
            <img src="assets/img/logo.png" alt="USTHB"><br>
            <div class="duplicata">DUPLICATA</div>
        </div>
        <div class="header-right">
            وزارة التعليم العالي والبحث العلمي<br>
            جامعة هواري بومدين للعلوم و التكنولوجيا الجزائر<br>
            كلية الإعلام الآلي<br>
            قسم الأنظمة المعلوماتية
        </div>
    </div>

    <div class="info-section">
        <div class="info-col">
            <div class="info-row"><div class="info-label">Année</div><div class="info-value">2024/2025</div></div>
            <div class="info-row"><div class="info-label">Nom:</div><div class="info-value"><?= strtoupper(htmlspecialchars($etudiant['nom'])) ?></div></div>
            <div class="info-row"><div class="info-label">N°d'inscription:</div><div class="info-value">UN16042024<?= htmlspecialchars($etudiant['matricule']) ?></div></div>
            <div class="info-row"><div class="info-label">Domaine:</div><div class="info-value">Mathématiques et Informatique</div></div>
            <div class="info-row"><div class="info-label">Diplôme préparé:</div><div class="info-value">Licence</div></div>
            <div class="info-row"><div class="info-label">Filière:</div><div class="info-value">Informatique</div></div>
        </div>
        <div class="info-col">
            <div class="title-center">RELEVE DE NOTES</div>
            <div class="info-row" style="margin-top: 5px;">
                <div class="info-label" style="width:60px;">Prénom:</div><div class="info-value"><?= strtoupper(htmlspecialchars($etudiant['prenom'])) ?></div>
                <div style="text-align: right; flex: 1;">Né(e) Le: <?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : '......' ?> à ............</div>
            </div>
            <div class="info-row"><div class="info-label" style="width:60px;">Niveau:</div><div class="info-value">Licence <?= htmlspecialchars($etudiant['niveau']) ?></div></div>
            <div class="info-row"><div class="info-label" style="width:60px;">Spécialité:</div><div class="info-value">Ingénierie des systèmes informatiques et logiciels</div></div>
        </div>
    </div>

    <?php 
        $semestres_data = [1 => [], 2 => []];
        foreach ($all_notes as $n) {
            $semestres_data[$n['semestre']][] = $n;
        }

        $ue_mapping = [
            1 => [
                ['nature' => 'U.E.F', 'code' => 'C00F0001S3', 'modules' => ['IS1', 'ARCHI1', 'ALGO3']],
                ['nature' => 'U.E.M', 'code' => 'C00M0001S3', 'modules' => ['PROBA', 'ANUM', 'LOGIQUE']],
                ['nature' => 'U.E.M', 'code' => 'C00M0002S3', 'modules' => ['POO']],
                ['nature' => 'U.E.T', 'code' => 'C00T0001S3', 'modules' => ['ANG1']]
            ],
            2 => [
                ['nature' => 'U.E.F', 'code' => 'C00F0001S4', 'modules' => ['GL1', 'BD1']],
                ['nature' => 'U.E.F', 'code' => 'C00F0002S4', 'modules' => ['ARCHI2', 'SYS1']],
                ['nature' => 'U.E.M', 'code' => 'C00M0001S4', 'modules' => ['THG', 'PWEB']],
                ['nature' => 'U.E.T', 'code' => 'C00T0001S4', 'modules' => ['ANG2']]
            ]
        ];

        $global_tot_credits = 0;
        $moyennes_sem = [1 => 0, 2 => 0];
        $credits_sem = [1 => 0, 2 => 0];
    ?>

    <table>
        <thead>
            <tr>
                <th colspan="7">Unité d'enseignement (U.E)</th>
                <th colspan="6">Matière(s) constitutive(s) de l'unité d'enseignement</th>
            </tr>
            <tr>
                <th>Nature</th>
                <th>Code Ue</th>
                <th>Crédits</th>
                <th>Coef</th>
                <th>Moy</th>
                <th>Crédits</th>
                <th>Sess</th>
                <th class="text-left">Intitulé(s)</th>
                <th>Crédits</th>
                <th>Coef</th>
                <th>Moy</th>
                <th>Crédits</th>
                <th>Sess</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ([1, 2] as $s_idx): ?>
                <?php 
                $s_display = (strpos($etudiant['niveau'], '2eme') !== false) ? ($s_idx + 2) : $s_idx;
                $s_notes = $semestres_data[$s_idx] ?? [];
                
                $ue_groups = [];
                $mapped_codes = [];
                foreach ($ue_mapping[$s_idx] as $ue_def) {
                    $group_notes = [];
                    foreach ($s_notes as $n) {
                        if (in_array($n['code_module'], $ue_def['modules'])) {
                            $group_notes[] = $n;
                            $mapped_codes[] = $n['code_module'];
                        }
                    }
                    if (!empty($group_notes)) {
                        $ue_groups[] = ['def' => $ue_def, 'notes' => $group_notes];
                    }
                }
                
                $unmapped = [];
                foreach ($s_notes as $n) {
                    if (!in_array($n['code_module'], $mapped_codes)) {
                        $unmapped[] = $n;
                    }
                }
                if (!empty($unmapped)) {
                    $ue_groups[] = ['def' => ['nature' => 'U.E.A', 'code' => 'AUTRE'], 'notes' => $unmapped];
                }
                
                $sem_tot_pts = 0;
                $sem_tot_coeff = 0;
                $sem_tot_credits_acquis = 0;
                
                foreach ($ue_groups as $group) {
                    $ue = $group['def'];
                    $notes = $group['notes'];
                    $rowspan = count($notes);
                    
                    $ue_coeff = 0; $ue_pts = 0; $ue_tot_credits = 0;
                    foreach ($notes as $n) {
                        $note_val = $n['note'] !== null ? (float)$n['note'] : 0.00;
                        $ue_coeff += $n['coefficient'];
                        $ue_pts += ($note_val * $n['coefficient']);
                        $ue_tot_credits += $n['credits'];
                    }
                    $ue_moy = $ue_coeff > 0 ? $ue_pts / $ue_coeff : 0;
                    
                    $ue_credits_acquis = 0;
                    if ($ue_moy >= 10) {
                        $ue_credits_acquis = $ue_tot_credits;
                    } else {
                        foreach ($notes as $n) {
                            $note_val = $n['note'] !== null ? (float)$n['note'] : 0.00;
                            if ($note_val >= 10) {
                                $ue_credits_acquis += $n['credits'];
                            }
                        }
                    }
                    
                    $sem_tot_pts += $ue_pts;
                    $sem_tot_coeff += $ue_coeff;
                    $sem_tot_credits_acquis += $ue_credits_acquis;
                    
                    $first = true;
                    foreach ($notes as $n) {
                        $note_val = $n['note'] !== null ? (float)$n['note'] : 0.00;
                        $mod_credits_acquis = ($ue_moy >= 10 || $note_val >= 10) ? $n['credits'] : 0;
                        
                        echo '<tr>';
                        if ($first) {
                            echo '<td rowspan="'.$rowspan.'">'.$ue['nature'].'</td>';
                            echo '<td rowspan="'.$rowspan.'">'.$ue['code'].'</td>';
                            echo '<td rowspan="'.$rowspan.'">'.str_pad($ue_tot_credits, 2, '0', STR_PAD_LEFT).'</td>';
                            echo '<td rowspan="'.$rowspan.'">'.number_format($ue_coeff, 1, '.', '').'</td>';
                            echo '<td rowspan="'.$rowspan.'">'.str_pad(number_format($ue_moy, 2, '.', ''), 5, '0', STR_PAD_LEFT).'</td>';
                            echo '<td rowspan="'.$rowspan.'">'.str_pad($ue_credits_acquis, 2, '0', STR_PAD_LEFT).'</td>';
                            echo '<td rowspan="'.$rowspan.'">N</td>';
                            $first = false;
                        }
                        
                        echo '<td class="text-left">'.htmlspecialchars($n['code_module'].' : '.$n['intitule']).'</td>';
                        echo '<td>'.number_format($n['credits'], 1, '.', '').'</td>';
                        echo '<td>'.number_format($n['coefficient'], 1, '.', '').'</td>';
                        echo '<td>'.str_pad(number_format($note_val, 2, '.', ''), 5, '0', STR_PAD_LEFT).'</td>';
                        echo '<td>'.number_format($mod_credits_acquis, 1, '.', '').'</td>';
                        echo '<td>N</td>';
                        echo '</tr>';
                    }
                }
                
                $sem_moy = $sem_tot_coeff > 0 ? $sem_tot_pts / $sem_tot_coeff : 0;
                $moyennes_sem[$s_idx] = $sem_moy;
                $credits_sem[$s_idx] = $sem_tot_credits_acquis;
                $global_tot_credits += $sem_tot_credits_acquis;
                ?>
                <tr class="semester-row">
                    <td colspan="4" class="text-left">Moyenne du Semestre <?= $s_display ?> :</td>
                    <td colspan="3" class="text-left"><?= str_pad(number_format($sem_moy, 2, '.', ''), 5, '0', STR_PAD_LEFT) ?></td>
                    <td colspan="4" class="text-right">Crédits du Semestre <?= $s_display ?> :</td>
                    <td colspan="1"><?= str_pad($sem_tot_credits_acquis, 2, '0', STR_PAD_LEFT) ?></td>
                    <td colspan="1">Session: N</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php 
        $moy_annuelle = ($moyennes_sem[1] + $moyennes_sem[2]) / 2;
        $admis = ($moy_annuelle >= 10);
        $credits_cursus = (strpos($etudiant['niveau'], '2eme') !== false ? 60 : 0) + $global_tot_credits;
    ?>

    <div class="footer-stats">
        <div style="width: 30%;">Moyenne &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= str_pad(number_format($moy_annuelle, 2, '.', ''), 5, '0', STR_PAD_LEFT) ?></div>
        <div style="width: 30%;">Décision: &nbsp;&nbsp; <?= $admis ? 'Admis(e)' : 'Ajourné(e)' ?></div>
        <div style="width: 40%; text-align: right; font-weight: normal;">N: Session Normale &nbsp;&nbsp; R: Session Rattrapage</div>
    </div>
    <div class="footer-stats" style="margin-top: 2px;">
        <div style="width: 30%;">Total des crédits cumulés pour l'année: &nbsp;&nbsp; <?= $global_tot_credits ?></div>
        <div style="width: 30%;">Total des crédits cumulés dans le cursus: <?= $credits_cursus ?></div>
        <div style="width: 40%;"></div>
    </div>

    <div style="margin-top: 20px; text-align: right; font-size: 11px; font-weight: bold; padding-right: 50px;">
        <p>Le chef de département</p>
        <br><br><br>
        <p>Le: <?= date('d-m-Y') ?></p>
    </div>
</div>

</body>
</html>
