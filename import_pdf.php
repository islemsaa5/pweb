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
if ($_SESSION['role'] !== 'admin') { header('Location: index.php'); exit; }

$page_title = 'Import PDF â€” Liste USTHB';
$result = ['inserted' => 0, 'skipped' => 0, 'errors' => []];
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['json_data'])) {
    $rows = json_decode($_POST['json_data'], true);

    if (!is_array($rows) || empty($rows)) {
        $result['errors'][] = 'Aucune donnée reçue ou format JSON invalide.';
    } else {
        $default_pw = password_hash('password', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO etudiants
                (matricule, nom, prenom, email, mot_de_passe, niveau, specialite, section, groupe_td, groupe_tp)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($rows as $r) {
            $matricule  = trim($r['matricule'] ?? '');
            $nom        = strtoupper(trim($r['nom'] ?? ''));
            $prenom     = trim($r['prenom'] ?? '');
            $section    = strtoupper(trim($r['section'] ?? 'A'));
            $palier     = trim($r['palier'] ?? 'L2');
            $specialite = trim($r['specialite'] ?? 'ISIL');
            $groupe_td  = (int)($r['groupe_td'] ?? 1);
            $groupe_tp  = (int)($r['groupe_tp'] ?? 1);

            if (empty($matricule) || empty($nom)) {
                $result['skipped']++;
                continue;
            }

            $niveau = match(true) {
                str_contains($palier, '1') => '1ere Annee',
                str_contains($palier, '3') => '3eme Annee',
                default => '2eme Annee',
            };

            $email = strtolower($matricule) . '@etud.usthb.dz';

            try {
                $stmt->execute([$matricule, $nom, $prenom, $email, $default_pw,
                                $niveau, $specialite, $section, $groupe_td ?: 1, $groupe_tp ?: 1]);
                $result['inserted']++;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $result['skipped']++;
                } else {
                    $result['errors'][] = "$matricule : " . $e->getMessage();
                }
            }
        }
    }
    $done = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import PDF USTHB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <style>
        body { background: #f5f7fa; }
        .import-pdf-wrap { max-width: 1000px; margin: 30px auto; padding: 0 20px; }

        .card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .card h2 { font-size: 16px; font-weight: 600; color: #2c3e80; margin-bottom: 16px; }

        
        #pdfDropZone {
            border: 2.5px dashed #2c3e80;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8faff;
            cursor: pointer;
            transition: all 0.2s;
        }
        #pdfDropZone:hover, #pdfDropZone.drag-over {
            background: #e8eeff;
            border-color: #1a2755;
        }
        #pdfDropZone .icon { font-size: 42px; color: #2c3e80; margin-bottom: 12px; }
        #pdfDropZone p { font-size: 15px; color: #555; margin: 4px 0; }
        #pdfDropZone small { color: #aaa; font-size: 12px; }

        
        #progressWrap { display:none; margin-top:15px; }
        #progressBar {
            height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;
        }
        #progressFill { height: 100%; background: #2c3e80; width: 0%; transition: width 0.3s; border-radius: 4px; }
        #progressMsg { font-size: 12px; color: #666; margin-top: 6px; text-align: center; }

        
        #previewSection { display: none; }
        #previewTable { font-size: 12px; }
        #previewTable th { background: #2c3e80; color: white; padding: 7px 10px; }
        #previewTable td { padding: 5px 10px; }
        #previewTable tr:nth-child(even) td { background: #f8fafc; }

        .stats-mini { display:flex; gap:15px; margin-bottom:15px; flex-wrap:wrap; }
        .stat-mini { background:#f0f4ff; border-radius:8px; padding:10px 16px; font-size:13px; }
        .stat-mini strong { display:block; font-size:20px; color:#2c3e80; }

        
        .btn-primary {
            background: #2c3e80; color: white; border: none; padding: 10px 22px;
            border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;
        }
        .btn-primary:hover { background: #1a2755; }
        .btn-success {
            background: #28a745; color: white; border: none; padding: 10px 22px;
            border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;
        }
        .btn-success:hover { background: #1e7e34; }
        .btn-back { 
            background: #6c757d; color: white; border: none; padding: 10px 18px;
            border-radius: 6px; font-size: 14px; cursor: pointer; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }

        
        #resultSection { display: none; }
        .result-box { 
            border-radius: 8px; padding: 20px; text-align: center; 
            border: 2px solid #28a745; background: #f0fff4;
        }
        .result-box .big-num { font-size: 48px; font-weight: 700; color: #2c3e80; }
        .result-box p { color: #555; margin: 6px 0; }

        .warning-msg { 
            background: #fff3cd; border: 1px solid #ffc107; border-left: 4px solid #fd7e14;
            border-radius: 6px; padding: 10px 14px; font-size: 13px; margin-bottom: 12px;
        }
        .error-list { background: #fff5f5; border: 1px solid #feb2b2; border-radius: 6px; padding: 12px; font-size: 12px; }
        .error-list li { color: #721c24; margin: 3px 0; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; include 'includes/sidebar.php'; ?>

<div style="flex:1; overflow:auto;">
<div class="import-pdf-wrap">

    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
        <a href="etudiants.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        <div>
            <h1 style="font-size:20px; font-weight:700; color:#1e293b;">Import PDF â€” Liste Officielle USTHB</h1>
            <p style="color:#64748b; font-size:13px;">Lisez et importez directement la liste PDF fournie par l'USTHB</p>
        </div>
    </div>

    <?php if ($done): ?>
    <!-- â”€â”€ Résultat de l'import â”€â”€ -->
    <div class="card">
        <div class="result-box">
            <div class="big-num"><?= $result['inserted'] ?></div>
            <p style="font-size:16px; font-weight:600;">étudiant(s) importé(s) avec succès</p>
            <?php if ($result['skipped'] > 0): ?>
            <p style="color:#888;"><?= $result['skipped'] ?> ligne(s) ignorée(s) (doublons)</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($result['errors'])): ?>
        <div class="error-list" style="margin-top:12px;">
            <ul><?php foreach ($result['errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
        <div style="margin-top:16px; display:flex; gap:10px;">
            <a href="etudiants.php" class="btn-success"><i class="fa-solid fa-users"></i> Voir la liste des étudiants</a>
            <a href="import_pdf.php" class="btn-primary"><i class="fa-solid fa-rotate"></i> Nouvel import</a>
        </div>
    </div>

    <?php else: ?>
    <!-- â”€â”€ Interface d'import â”€â”€ -->

    <!-- Étape 1 : Sélection du fichier -->
    <div class="card" id="step1">
        <h2><span style="background:#2c3e80;color:white;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">1</span>Sélectionnez votre fichier PDF</h2>

        <div class="warning-msg">
            <i class="fa-solid fa-circle-info"></i>
            Fonctionne avec le PDF officiel USTHB contenant la liste des étudiants (texte sélectionnable).
            <strong>Ne fonctionne pas sur les PDF scannés (images).</strong>
        </div>

        <div id="pdfDropZone" onclick="document.getElementById('pdfInput').click()">
            <div class="icon"><i class="fa-solid fa-file-pdf"></i></div>
            <p><strong>Cliquez ici</strong> ou glissez-déposez votre PDF</p>
            <small>Format : Liste étudiants USTHB (.pdf)</small>
        </div>
        <input type="file" id="pdfInput" accept=".pdf" style="display:none;">

        <div id="progressWrap">
            <div id="progressBar"><div id="progressFill"></div></div>
            <div id="progressMsg">Lecture du PDF...</div>
        </div>
    </div>

    <!-- Étape 2 : Prévisualisation -->
    <div class="card" id="previewSection">
        <h2><span style="background:#2c3e80;color:white;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:12px;margin-right:8px;">2</span>Données extraites â€” Vérifiez avant d'importer</h2>

        <div class="stats-mini">
            <div class="stat-mini"><strong id="countExtracted">0</strong>étudiants détectés</div>
            <div class="stat-mini"><strong id="countPages">0</strong>page(s) lues</div>
        </div>

        <div style="max-height:400px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
            <table class="w-full" id="previewTable" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Section</th>
                        <th>G.TD</th>
                        <th>G.TP</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody id="previewBody"></tbody>
            </table>
        </div>

        <div id="noDataWarning" style="display:none; text-align:center; padding:20px; color:#888;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size:24px; color:#ffc107;"></i>
            <p style="margin-top:8px;">Aucune donnée étudiant détectée dans ce PDF.<br>
            <small>Le PDF est peut-être scanné (image) ou de format différent.</small></p>
        </div>

        <form method="POST" id="importForm" style="margin-top:16px;">
            <input type="hidden" name="json_data" id="jsonData">
            <div style="display:flex; gap:10px; align-items:center;">
                <button type="submit" class="btn-success" id="btnImport" disabled>
                    <i class="fa-solid fa-database"></i> Importer dans la base de données
                </button>
                <button type="button" class="btn-primary" onclick="resetImport()">
                    <i class="fa-solid fa-rotate"></i> Recommencer
                </button>
            </div>
        </form>
    </div>

    <?php endif; ?>
</div>
</div>

<script>

pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let extractedStudents = [];

const dropZone = document.getElementById('pdfDropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file && file.type === 'application/pdf') processPDF(file);
    });
}

document.getElementById('pdfInput')?.addEventListener('change', function() {
    if (this.files[0]) processPDF(this.files[0]);
});

function setProgress(pct, msg) {
    document.getElementById('progressWrap').style.display = 'block';
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressMsg').textContent = msg;
}

async function processPDF(file) {
    setProgress(5, 'Chargement du fichier PDF...');
    const arrayBuffer = await file.arrayBuffer();
    
    let pdf;
    try {
        pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    } catch(e) {
        alert('Impossible de lire ce PDF. Est-il protégé ou scanné (image) ?');
        return;
    }

    const totalPages = pdf.numPages;
    document.getElementById('countPages').textContent = totalPages;
    
    let allText = [];

    for (let p = 1; p <= totalPages; p++) {
        setProgress(Math.round((p / totalPages) * 80), `Lecture page ${p}/${totalPages}...`);
        const page = await pdf.getPage(p);
        const textContent = await page.getTextContent();

        const items = textContent.items.map(item => ({
            text: item.str,
            x: Math.round(item.transform[4]),
            y: Math.round(item.transform[5])
        }));
        
        allText.push(...items);
    }

    setProgress(90, 'Analyse des données...');
    extractedStudents = parseUSThBData(allText);
    setProgress(100, `âœ… Terminé â€” ${extractedStudents.length} étudiant(s) détecté(s)`);

    displayPreview(extractedStudents);
}

function parseUSThBData(items) {
    const students = [];

    const lines = {};
    items.forEach(item => {
        if (!item.text.trim()) return;

        let key = null;
        for (const k of Object.keys(lines)) {
            if (Math.abs(parseInt(k) - item.y) <= 4) { key = k; break; }
        }
        if (!key) { key = item.y; lines[key] = []; }
        lines[key].push(item);
    });

    const sortedKeys = Object.keys(lines).sort((a,b) => parseFloat(b) - parseFloat(a));

    const MATRICULE_RE = /^2[012]\d{8,12}$/;

    for (const key of sortedKeys) {
        const lineItems = lines[key].sort((a,b) => a.x - b.x);
        const lineText = lineItems.map(i => i.text.trim()).filter(Boolean);

        const matIdx = lineText.findIndex(t => MATRICULE_RE.test(t.replace(/\s/g,'')));
        if (matIdx < 0) continue;

        const matricule = lineText[matIdx].replace(/\s/g,'');

        let section = 'A';
        for (let i = Math.max(0, matIdx-4); i < matIdx; i++) {
            if (/^[A-G]$/.test(lineText[i])) { section = lineText[i]; break; }
        }

        let palier = 'L2', specialite = 'ISIL';
        for (let i = 0; i < Math.min(matIdx, 6); i++) {
            if (/^L[123]$/.test(lineText[i])) palier = lineText[i];
            if (/^[A-Z]{2,6}$/.test(lineText[i]) && lineText[i] !== palier && !/^[A-G]$/.test(lineText[i])) {
                specialite = lineText[i];
            }
        }


        let nom = '', prenom = '', etat = '', groupe_td = '', groupe_tp = '';
        const after = lineText.slice(matIdx + 1);

        const etatIdx = after.findIndex(t => /^(ADM|AJR|ADC)$/i.test(t));

        if (etatIdx >= 0) {
            etat = after[etatIdx].toUpperCase();

            const nameTokens = after.slice(0, etatIdx).filter(t => !/^\d+$/.test(t));
            if (nameTokens.length >= 2) {

                nom = nameTokens[0];
                prenom = nameTokens.slice(1).join(' ');
            } else if (nameTokens.length === 1) {
                nom = nameTokens[0];
            }

            const afterEtat = after.slice(etatIdx + 1).filter(t => /^\d+$/.test(t));
            if (afterEtat.length >= 1) groupe_td = afterEtat[0];
            if (afterEtat.length >= 2) groupe_tp = afterEtat[1];
        } else {

            const nameTokens = after.filter(t => !/^\d+$/.test(t) && t.length > 1);
            if (nameTokens.length >= 1) nom = nameTokens[0];
            if (nameTokens.length >= 2) prenom = nameTokens.slice(1).join(' ');
        }

        if (nom) {
            students.push({ matricule, nom, prenom, etat, section, palier, specialite,
                           groupe_td: parseInt(groupe_td)||1, groupe_tp: parseInt(groupe_tp)||1 });
        }
    }

    return students;
}

function displayPreview(students) {
    document.getElementById('previewSection').style.display = 'block';
    document.getElementById('countExtracted').textContent = students.length;
    
    const tbody = document.getElementById('previewBody');
    tbody.innerHTML = '';

    if (students.length === 0) {
        document.getElementById('noDataWarning').style.display = 'block';
        document.getElementById('btnImport').disabled = true;
        return;
    }

    document.getElementById('noDataWarning').style.display = 'none';
    document.getElementById('btnImport').disabled = false;

    students.forEach((s, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="text-align:center;color:#888;">${i+1}</td>
            <td><code style="font-size:11px;">${s.matricule}</code></td>
            <td><strong>${s.nom}</strong></td>
            <td>${s.prenom}</td>
            <td style="text-align:center;">
                <span style="display:inline-block;width:22px;height:22px;border-radius:50%;background:#2c3e80;color:white;font-weight:700;font-size:11px;line-height:22px;text-align:center;">${s.section}</span>
            </td>
            <td style="text-align:center;">${s.groupe_td||''}</td>
            <td style="text-align:center;">${s.groupe_tp||''}</td>
            <td style="text-align:center;font-weight:700;color:${s.etat==='ADM'?'#155724':s.etat==='AJR'?'#721c24':'#666'}">${s.etat||'-'}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('jsonData').value = JSON.stringify(students);
}

function resetImport() {
    document.getElementById('previewSection').style.display = 'none';
    document.getElementById('progressWrap').style.display = 'none';
    document.getElementById('progressFill').style.width = '0%';
    document.getElementById('pdfInput').value = '';
    extractedStudents = [];
}
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
