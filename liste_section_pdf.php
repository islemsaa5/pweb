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

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$section_filter = $_GET['section'] ?? 'all';

if ($section_filter !== 'all') {
    $stmt = $pdo->prepare("
        SELECT e.*,
        (SELECT AVG(note) FROM notes WHERE etudiant_id = e.id) as moy_annuelle
        FROM etudiants e WHERE section = ? ORDER BY nom ASC
    ");
    $stmt->execute([$section_filter]);
} else {
    $stmt = $pdo->query("
        SELECT e.*,
        (SELECT AVG(note) FROM notes WHERE etudiant_id = e.id) as moy_annuelle
        FROM etudiants e ORDER BY section ASC, nom ASC
    ");
}
$etudiants = $stmt->fetchAll();

$specialite = !empty($etudiants[0]['specialite']) ? $etudiants[0]['specialite'] : 'ISIL';
$niveau_label = 'L2';
if (!empty($etudiants[0]['niveau'])) {
    if (stripos($etudiants[0]['niveau'], '1') !== false)      $niveau_label = 'L1';
    elseif (stripos($etudiants[0]['niveau'], '3') !== false)  $niveau_label = 'L3';
}
$section_label = ($section_filter !== 'all') ? 'Section ' . strtoupper($section_filter) : 'Toutes Sections';
$page_title = "Liste des étudiants : $niveau_label $specialite" . ($section_filter !== 'all' ? " $section_filter" : '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
            font-size: 11px;
            color: #000;
        }

        
        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-print {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover { background: var(--primary-dark); }

        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .section-filter {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-left: auto;
        }

        .section-filter label { font-weight: 600; font-size: 13px; }

        .section-filter select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 13px;
            cursor: pointer;
        }

        
        .a4-page {
            background: white;
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            padding: 10mm 8mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        
        .official-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-left img {
            width: 70px;
            height: auto;
        }

        .header-center {
            text-align: center;
            flex: 1;
        }

        .header-center .univ-name {
            font-size: 10px;
            font-weight: 700;
            color: #000;
            line-height: 1.4;
        }

        .header-center .year {
            font-size: 9px;
            color: #333;
            margin-top: 2px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 8px;
            text-align: right;
        }

        .header-right .fac-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1.3;
            border: 2px solid var(--primary-color);
            padding: 4px 8px;
            text-align: center;
        }

        .header-right img {
            width: 50px;
            height: auto;
        }

        
        .list-title {
            text-align: center;
            margin: 6px 0 5px;
        }

        .list-title h1 {
            font-size: 13px;
            font-weight: 700;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-title .version {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }

        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        th, td {
            border: 1px solid #333;
            padding: 2px 3px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        thead tr:first-child th {
            background: #dce1f0;
            font-weight: 700;
            font-size: 8px;
        }

        thead tr:last-child th {
            background: #eef0f8;
            font-weight: 700;
            font-size: 7.5px;
        }

        td.nom-cell { text-align: left; font-weight: 600; min-width: 28mm; }
        td.prenom-cell { text-align: left; min-width: 22mm; }

        tr:nth-child(even) td { background: #f9f9ff; }
        tr:nth-child(odd) td { background: #fff; }

        
        .pres-header { background: #c8d0e8 !important; }
        .pres-subheader { background: #dce6f5 !important; font-size: 7px !important; }
        .pres-col { width: 6mm; min-width: 6mm; font-size: 7px; color: #aaa; }

        
        .etat-adm { color: #155724; font-weight: 700; }
        .etat-ajr { color: #721c24; font-weight: 700; }
        .etat-adc { color: #856404; font-weight: 700; }

        
        .col-n { width: 6mm; font-size: 8px; color: #555; }
        .col-palier { width: 8mm; }
        .col-spe { width: 12mm; }
        .col-sec { width: 10mm; }
        .col-mat { width: 26mm; font-family: monospace; font-size: 8px; }
        .col-etat { width: 10mm; }
        .col-gtd { width: 14mm; }
        .col-gtp { width: 14mm; }

        
        .footer-line {
            margin-top: 5px;
            border-top: 1px solid #999;
            padding-top: 3px;
            display: flex;
            justify-content: space-between;
            font-size: 7.5px;
            color: #555;
        }

        
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            body {
                background: white;
                padding: 0;
                font-size: 10px;
            }

            .controls { display: none !important; }

            .a4-page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<!-- Contrôles (masqués à l'impression) -->
<div class="controls">
    <a href="etudiants.php" class="btn-back">← Retour</a>
    <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Télécharger / Imprimer PDF</button>

    <div class="section-filter">
        <label>Section :</label>
        <select onchange="window.location.href='liste_section_pdf.php?section='+this.value">
            <option value="all" <?= $section_filter === 'all' ? 'selected' : '' ?>>Toutes</option>
            <option value="A" <?= $section_filter === 'A' ? 'selected' : '' ?>>Section A</option>
            <option value="B" <?= $section_filter === 'B' ? 'selected' : '' ?>>Section B</option>
            <option value="C" <?= $section_filter === 'C' ? 'selected' : '' ?>>Section C</option>
        </select>
    </div>

    <span style="font-size:12px; color:#666; margin-left:10px;">
        ðŸ“‹ <?= count($etudiants) ?> étudiant(s) â€” <?= $section_label ?>
    </span>
</div>

<!-- Feuille A4 paysage -->
<div class="a4-page">

    <!-- En-tête officiel -->
    <div class="official-header">
        <div class="header-left">
            <img src="assets/img/logo.png" alt="USTHB">
            <div>
                <div style="font-size:8px; font-weight:700; line-height:1.3; color:#000;">USTHB</div>
                <div style="font-size:7px; color:#444;">Bab Ezzouar, Alger</div>
            </div>
        </div>

        <div class="header-center">
            <div class="univ-name">
                Université des Sciences et de la Technologie Houari Boumediene<br>
                Faculté d'Informatique
            </div>
            <div class="year">Année Universitaire 2025/2026</div>
        </div>

        <div class="header-right">
            <div class="fac-label">FACULTÉ<br>D'INFORMATIQUE</div>
            <img src="assets/img/logo.png" alt="Logo Faculté">
        </div>
    </div>

    <!-- Titre -->
    <div class="list-title">
        <h1>Liste des étudiants : <?= htmlspecialchars($niveau_label . ' ' . $specialite) ?>
            <?= $section_filter !== 'all' ? htmlspecialchars(' ' . strtoupper($section_filter)) : '' ?>
        </h1>
        <div class="version">Version <?= date('d/m/Y H:i') ?></div>
    </div>

    <!-- Tableau des étudiants -->
    <table>
        <thead>
            <!-- Ligne 1 : en-têtes principales -->
            <tr>
                <th class="col-n" rowspan="2">N°</th>
                <th class="col-palier" rowspan="2">Palier</th>
                <th class="col-spe" rowspan="2">Spécialité</th>
                <th class="col-sec" rowspan="2">Section</th>
                <th class="col-mat" rowspan="2">Matricule</th>
                <th style="min-width:30mm;" rowspan="2">Nom</th>
                <th style="min-width:25mm;" rowspan="2">Prénom</th>
                <th class="col-etat" rowspan="2">État</th>
                <th class="col-gtd" rowspan="2">Groupe TD</th>
                <th class="col-gtp" rowspan="2">Groupe TP</th>
                <!-- Colonnes Présences -->
                <?php for ($s = 1; $s <= 14; $s++): ?>
                <th colspan="3" class="pres-header">Sem <?= $s ?></th>
                <?php endfor; ?>
            </tr>
            <!-- Ligne 2 : sous-colonnes C / TD / TP -->
            <tr>
                <?php for ($s = 1; $s <= 14; $s++): ?>
                <th class="pres-subheader pres-col">C</th>
                <th class="pres-subheader pres-col">TD</th>
                <th class="pres-subheader pres-col">TP</th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php $n = 1; foreach ($etudiants as $e):
                $moy = $e['moy_annuelle'];
                if ($moy === null)       $etat = '';
                elseif ($moy >= 10)      $etat = 'ADM';
                else                     $etat = 'AJR';

                $etat_class = match($etat) {
                    'ADM' => 'etat-adm',
                    'AJR' => 'etat-ajr',
                    'ADC' => 'etat-adc',
                    default => ''
                };

                $palier = 'L2';
                if (stripos($e['niveau'] ?? '', '1') !== false)      $palier = 'L1';
                elseif (stripos($e['niveau'] ?? '', '3') !== false)  $palier = 'L3';
            ?>
            <tr>
                <td class="col-n"><?= $n++ ?></td>
                <td class="col-palier"><?= htmlspecialchars($palier) ?></td>
                <td class="col-spe"><?= htmlspecialchars($e['specialite'] ?? 'ISIL') ?></td>
                <td class="col-sec"><?= htmlspecialchars(strtoupper($e['section'] ?? '')) ?></td>
                <td class="col-mat"><?= htmlspecialchars($e['matricule']) ?></td>
                <td class="nom-cell"><?= htmlspecialchars($e['nom']) ?></td>
                <td class="prenom-cell"><?= htmlspecialchars($e['prenom']) ?></td>
                <td class="col-etat <?= $etat_class ?>"><?= $etat ?></td>
                <td class="col-gtd"><?= $e['groupe_td'] ?? '' ?></td>
                <td class="col-gtp"><?= $e['groupe_tp'] ?? '' ?></td>
                <!-- 14 Ã— 3 colonnes de présence vides -->
                <?php for ($s = 1; $s <= 14; $s++): ?>
                <td class="pres-col"></td>
                <td class="pres-col"></td>
                <td class="pres-col"></td>
                <?php endfor; ?>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($etudiants)): ?>
            <tr>
                <td colspan="52" style="text-align:center; padding:20px; color:#888;">
                    Aucun étudiant trouvé pour cette section.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer-line">
        <span>Faculté d'Informatique â€” USTHB â€” Département Informatique</span>
        <span>Généré le <?= date('d/m/Y à H:i') ?></span>
        <span>Total : <?= count($etudiants) ?> étudiant(s)</span>
    </div>

</div><!-- fin .a4-page -->

</body>
</html>
