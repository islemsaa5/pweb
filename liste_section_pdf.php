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
        (SELECT SUM(n.note * m.coefficient) / SUM(m.coefficient) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id) as moy_annuelle
        FROM etudiants e WHERE section = ? ORDER BY nom ASC
    ");
    $stmt->execute([$section_filter]);
} else {
    $stmt = $pdo->query("
        SELECT e.*,
        (SELECT SUM(n.note * m.coefficient) / SUM(m.coefficient) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id) as moy_annuelle
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f0f0f0;
            padding: 20px;
            font-size: 11px;
            color: #000;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .controls {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-print {
            background: #2c3e80;
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
            transition: 0.2s;
        }

        .btn-print:hover { background: #1a254d; }

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
            padding: 10mm 15mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .republique-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .republique-header h2 {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .republique-header h3 {
            font-size: 12px;
            font-weight: 500;
            margin-top: 2px;
            color: #333;
        }

        .official-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2c3e80;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header-left, .header-right {
            width: 120px;
            display: flex;
            justify-content: center;
        }

        .header-left img, .header-right img {
            width: 80px;
            height: auto;
            object-fit: contain;
        }

        .header-center {
            text-align: center;
            flex: 1;
        }

        .header-center .univ-name {
            font-size: 13px;
            font-weight: 700;
            color: #000;
            line-height: 1.4;
        }

        .header-center .fac-name {
            font-size: 12px;
            font-weight: 600;
            color: #2c3e80;
            margin-top: 4px;
        }

        .header-center .year {
            font-size: 11px;
            color: #555;
            margin-top: 4px;
            font-style: italic;
        }

        .list-title {
            text-align: center;
            margin: 15px 0;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
            padding: 10px;
            border-radius: 6px;
        }

        .list-title h1 {
            font-size: 16px;
            font-weight: 700;
            color: #2c3e80;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .list-title .version {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 15px;
        }

        th, td {
            border: 1px solid #777;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }

        thead tr:first-child th {
            background: #dce1f0 !important;
            font-weight: 700;
            font-size: 10px;
            color: #2c3e80;
        }

        thead tr:last-child th {
            background: #eef0f8 !important;
            font-weight: 700;
            font-size: 8px;
        }

        td.nom-cell { text-align: left; font-weight: 600; min-width: 30mm; }
        td.prenom-cell { text-align: left; min-width: 25mm; }

        tr:nth-child(even) td { background: #f9f9ff !important; }
        tr:nth-child(odd) td { background: #fff !important; }

        .pres-header { background: #c8d0e8 !important; }
        .pres-subheader { background: #dce6f5 !important; font-size: 8px !important; }
        .pres-col { width: 6mm; min-width: 6mm; font-size: 7px; color: #aaa; }

        .etat-adm { color: #155724; font-weight: 700; }
        .etat-ajr { color: #721c24; font-weight: 700; }
        .etat-adc { color: #856404; font-weight: 700; }

        .col-n { width: 6mm; font-size: 9px; color: #555; }
        .col-palier { width: 10mm; }
        .col-spe { width: 12mm; }
        .col-sec { width: 12mm; font-weight: bold; }
        .col-mat { width: 28mm; font-family: monospace; font-size: 10px; font-weight: bold; }
        .col-etat { width: 12mm; }
        .col-gtd { width: 14mm; }
        .col-gtp { width: 14mm; }

        .footer-line {
            margin-top: 15px;
            border-top: 1px solid #2c3e80;
            padding-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #444;
            font-weight: 500;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }

            body {
                background: white;
                padding: 0;
            }

            .controls { display: none !important; }

            .a4-page {
                width: 100%;
                min-height: auto;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            thead {
                display: table-header-group;
            }
            
            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>
<body>

<!-- Contrôles (masqués à l'impression) -->
<div class="controls">
    <a href="etudiants.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
    <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimer / Sauvegarder en PDF</button>

    <div class="section-filter">
        <label><i class="fa-solid fa-filter"></i> Section :</label>
        <select onchange="window.location.href='liste_section_pdf.php?section='+this.value">
            <option value="all" <?= $section_filter === 'all' ? 'selected' : '' ?>>Toutes les sections</option>
            <option value="A" <?= $section_filter === 'A' ? 'selected' : '' ?>>Section A</option>
            <option value="B" <?= $section_filter === 'B' ? 'selected' : '' ?>>Section B</option>
            <option value="C" <?= $section_filter === 'C' ? 'selected' : '' ?>>Section C</option>
        </select>
    </div>

    <span style="font-size:13px; font-weight:bold; color:#2c3e80; margin-left:15px; border-left: 2px solid #ccc; padding-left: 15px;">
        <i class="fa-solid fa-users"></i> <?= count($etudiants) ?> étudiant(s) au total
    </span>
</div>

<!-- Feuille A4 paysage -->
<div class="a4-page">

    <!-- En-tête République -->
    <div class="republique-header">
        <h2>République Algérienne Démocratique et Populaire</h2>
        <h3>Ministère de l'Enseignement Supérieur et de la Recherche Scientifique</h3>
    </div>

    <!-- En-tête officiel USTHB -->
    <div class="official-header">
        <div class="header-left">
            <img src="assets/img/logo.png" alt="USTHB Logo">
        </div>

        <div class="header-center">
            <div class="univ-name">Université des Sciences et de la Technologie Houari Boumediene</div>
            <div class="fac-name">Faculté d'Informatique</div>
            <div class="year">Année Universitaire 2025/2026</div>
        </div>

        <div class="header-right">
            <img src="assets/img/logo.png" alt="Faculté Logo">
        </div>
    </div>

    <!-- Titre -->
    <div class="list-title">
        <h1>Liste Officielle des Étudiants : <?= htmlspecialchars($niveau_label . ' ' . $specialite) ?>
            <?= $section_filter !== 'all' ? htmlspecialchars(' — ' . strtoupper($section_filter)) : '' ?>
        </h1>
        <div class="version">Document généré le <?= date('d/m/Y à H:i') ?></div>
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
                <td class="nom-cell"><?= htmlspecialchars(strtoupper($e['nom'])) ?></td>
                <td class="prenom-cell"><?= htmlspecialchars(ucfirst(strtolower($e['prenom']))) ?></td>
                <td class="col-etat <?= $etat_class ?>"><?= $etat ?></td>
                <td class="col-gtd"><?= $e['groupe_td'] ?? '' ?></td>
                <td class="col-gtp"><?= $e['groupe_tp'] ?? '' ?></td>
                <!-- 14 x 3 colonnes de présence vides -->
                <?php for ($s = 1; $s <= 14; $s++): ?>
                <td class="pres-col"></td>
                <td class="pres-col"></td>
                <td class="pres-col"></td>
                <?php endfor; ?>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($etudiants)): ?>
            <tr>
                <td colspan="52" style="text-align:center; padding:30px; color:#888; font-size: 12px; font-style: italic;">
                    Aucun étudiant trouvé pour cette section.
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pied de page -->
    <div class="footer-line">
        <span>Faculté d'Informatique — USTHB — Département Informatique</span>
        <span>Total : <?= count($etudiants) ?> étudiant(s)</span>
        <span>USTHB Scolarité - Document Officiel</span>
    </div>

</div><!-- fin .a4-page -->

</body>
</html>
