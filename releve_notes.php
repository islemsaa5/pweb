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
    SELECT n.note, m.intitule, m.coefficient, m.credits, m.semestre, m.code_module
    FROM notes n 
    JOIN modules m ON n.module_id = m.id 
    WHERE n.etudiant_id = ?
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f4f7f6; padding: 30px; color: #333; }
        .releve-paper { background: white; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 10px; border-top: 5px solid var(--primary-color); position: relative; }
        
        
        .official-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--primary-color); padding-bottom: 20px; margin-bottom: 30px; }
        .official-header img { width: 90px; height: auto; object-fit: contain; }
        .official-header .text { flex: 1; padding: 0 20px; font-size: 13px; line-height: 1.4; color: var(--primary-color); font-weight: 500; }
        
        .releve-title { text-align: center; margin-bottom: 30px; }
        .releve-title h1 { font-size: 28px; color: var(--primary-color); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; border-bottom: 2px solid #ccc; display: inline-block; padding-bottom: 5px; }
        
        
        .student-box { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e2e8f0; }
        .student-box p { margin: 5px 0; font-size: 14px; }
        .student-box strong { color: var(--primary-color); }

        
        table { 
            width: 100%; 
            border-collapse: separate; 
            border-spacing: 2px;
            border: 1px solid #000;
            margin-bottom: 15px; 
            background: #fff;
        }
        th { 
            background: #fff; 
            color: #000; 
            font-weight: 700; 
            text-align: center; 
            padding: 10px; 
            border: 1px solid #000; 
            font-size: 12px; 
            text-transform: uppercase; 
        }
        td { 
            padding: 10px; 
            border: 1px solid #333; 
            font-size: 12px; 
            color: #000;
        }
        tr:nth-child(even) { background: #fff; }
        
        .result-row td { 
            background: #f8fafc !important; 
            font-weight: 700; 
            border: 2px solid #000;
        }
        
        
        .final-summary { margin-top: 40px; padding: 25px; border: 2px solid #000; border-radius: 4px; background: #fff; display: flex; justify-content: space-between; align-items: center; }
        .final-summary .decision { font-size: 18px; font-weight: 700; color: #000; }
        .final-summary .statut-badge { padding: 8px 15px; border-radius: 4px; border: 2px solid #000; color: #000; font-weight: 700; font-size: 16px; }
        .statut-admis { background: var(--valid-green); color: white; }
        .statut-ajourne { background: var(--invalid-red); color: white; }

<button class="btn-print no-print" onclick="window.print()">??? Télécharger en PDF</button>
<button class="btn-print no-print" onclick="window.print()">??? Télécharger en PDF</button>

        @media print {
            body { background: white; padding: 0; }
            .releve-paper { box-shadow: none; margin: 0; width: 100%; border: none; padding: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<button class="btn-print no-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Télécharger en PDF</button>

<div class="releve-paper">
    <div class="official-header">
        <div class="text" style="text-align: left;">
            <p>République Algérienne Démocratique et Populaire</p>
            <p>Ministère de l'Enseignement Supérieur et de la Recherche Scientifique</p>
            <p><strong>USTHB - Faculté d'Informatique</strong></p>
        </div>
        <img src="assets/img/logo.png" alt="Logo USTHB">
        <div class="text" style="text-align: right;">
            <p>الجمهورية الجزائرية الديمقراطية الشعبية</p>
            <p>وزارة التعليم العالي والبحث العلمي</p>
            <p><strong>جامعة هواري بومدين للعلوم و التكنولوجيا</strong></p>
        </div>
    </div>

    <div class="releve-title">
        <h1>Relevé de Notes Annuel</h1>
        <p style="margin-top: 10px; color: #64748b;">Année Académique : <strong>2025/2026</strong></p>
    </div>

    <div class="student-box">
        <div>
            <p><strong>Nom :</strong> <?= htmlspecialchars($etudiant['nom']) ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars($etudiant['prenom']) ?></p>
            <p><strong>Né(e) le :</strong> <?= $etudiant['date_naissance'] ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : 'Non renseigné' ?></p>
        </div>
        <div>
            <p><strong>Matricule :</strong> <?= htmlspecialchars($etudiant['matricule']) ?></p>
            <p><strong>Niveau :</strong> <?= htmlspecialchars($etudiant['niveau']) ?></p>
            <p><strong>Section :</strong> Section <?= htmlspecialchars($etudiant['section']) ?></p>
        </div>
    </div>

    <?php 
        $tot_pts = 0; $tot_coeff = 0; $tot_creds = 0;
    ?>

    <?php foreach ([1, 2] as $s): ?>
        <div class="semestre-title">SEMESTRE <?= $s ?></div>
        <table>
            <thead>
                <tr>
                    <th style="width: 45%;">Module</th>
                    <th style="width: 15%; text-align: center;">Note</th>
                    <th style="width: 15%; text-align: center;">Coefficient</th>
                    <th style="width: 15%; text-align: center;">Crédits</th>
                    <th style="width: 10%; text-align: center;">État</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $s_pts = 0; $s_coeff = 0; $s_creds_obtenus = 0;
                if (!empty($semestres[$s])):
                foreach ($semestres[$s] as $n): 
                    $s_pts += ($n['note'] * $n['coefficient']);
                    $s_coeff += $n['coefficient'];
                    $cred_obtenu = ($n['note'] >= 10) ? $n['credits'] : 0;
                    $s_creds_obtenus += $cred_obtenu;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($n['intitule']) ?></strong></td>
                    <td style="text-align: center; color: <?= $n['note'] >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>; font-weight: 600;">
                        <?= number_format($n['note'], 2) ?>
                    </td>
                    <td style="text-align: center;"><?= $n['coefficient'] ?></td>
                    <td style="text-align: center;"><?= $n['credits'] ?> (<?= $cred_obtenu ?>)</td>
                    <td style="text-align: center;">
                        <span style="color: <?= $n['note'] >= 10 ? 'var(--valid-green)' : 'var(--invalid-red)' ?>; font-weight: bold;">
                            <?= $n['note'] >= 10 ? 'V' : 'X' ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; endif; ?>

                <?php 
                    $s_moy = $s_coeff > 0 ? $s_pts / $s_coeff : 0;
                    $tot_pts += $s_pts;
                    $tot_coeff += $s_coeff;
                    $tot_creds += $s_creds_obtenus;
                ?>
                <tr class="result-row">
                    <td>Moyenne Semestrielle <?= $s ?></td>
                    <td style="text-align: center; background: #e2e8f0;"><?= number_format($s_moy, 2) ?></td>
                    <td colspan="2" style="text-align: right;">Crédits obtenus :</td>
                    <td style="text-align: center; background: #e2e8f0;"><?= $s_creds_obtenus ?></td>
                </tr>
            </tbody>
        </table>
    <?php endforeach; ?>

    <?php 
        $moy_annuelle = $tot_coeff > 0 ? $tot_pts / $tot_coeff : 0;
        $admis = ($moy_annuelle >= 10);
    ?>

    <div class="final-summary">
        <div>
            <div class="decision">MOYENNE ANNUELLE : <?= number_format($moy_annuelle, 2) ?> / 20</div>
            <p style="margin-top: 5px; color: #64748b;">Total Crédits Cumulés : <strong><?= $tot_creds ?></strong></p>
        </div>
        <div class="statut-badge <?= $admis ? 'statut-admis' : 'statut-ajourne' ?>">
            DÉCISION : <?= $admis ? 'ADMIS(E)' : 'AJOURNÉ(E)' ?>
        </div>
    </div>

    <div style="margin-top: 50px; display: flex; justify-content: space-between; font-size: 13px;">
        <div style="text-align: center;">
            <p>Le Chef de Département</p>
            <br><br>
            <p>_______________________</p>
        </div>
        <div style="text-align: right;">
            <p>Fait à Alger, le <?= date('d/m/Y') ?></p>
            <p>PWEB Project Management System</p>
        </div>
    </div>
</div>

</body>
</html>
