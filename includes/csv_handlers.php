<?php
/**
 * Projet: Gestion de Scolarité USTHB
 * Équipe:
 * - SAADI Islem (232331698506)
 * - KHELLAS Maria (242431486807)
 * - ABDELLATIF Sara (242431676416)
 * - DAHMANI Anais (242431679715)
 */
function exportEtudiantsCSV($pdo, $section_filter = 'all') {
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
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $section_label = ($section_filter !== 'all') ? 'Section ' . $section_filter : 'Toutes sections';
    $filename = 'liste_etudiants_' . ($section_filter !== 'all' ? 'Sec'.$section_filter : 'tous') . '_' . date('Ymd') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['Université des Sciences et de la Technologie Houari Boumediene'], ';');
    fputcsv($out, ['Faculté d\'Informatique'], ';');
    fputcsv($out, ['Année Universitaire 2025/2026'], ';');
    fputcsv($out, ['Liste des étudiants - ' . $section_label], ';');
    fputcsv($out, ['Exporté le : ' . date('d/m/Y H:i')], ';');
    fputcsv($out, [], ';');

    fputcsv($out, ['N°','Palier','Spécialité','Section','Matricule','Nom','Prénom','État','Groupe TD','Groupe TP'], ';');

    $n = 1;
    foreach ($rows as $row) {
        $moy = $row['moy_annuelle'];
        $etat = ($moy === null) ? '' : (($moy >= 10) ? 'ADM' : 'AJR');

        $palier = 'L2';
        if (stripos($row['niveau'] ?? '', '1') !== false)      $palier = 'L1';
        elseif (stripos($row['niveau'] ?? '', '3') !== false)  $palier = 'L3';

        fputcsv($out, [
            $n++, $palier, $row['specialite'] ?? 'ISIL', $row['section'] ?? '',
            $row['matricule'], $row['nom'], $row['prenom'], $etat,
            $row['groupe_td'] ?? 1, $row['groupe_tp'] ?? 1
        ], ';');
    }
    fclose($out);
    exit;
}


function importEtudiantsCSV($pdo, $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Erreur de téléchargement.'];
    if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') return ['error' => 'Le fichier doit être au format .csv'];

    $handle = fopen($file['tmp_name'], 'r');
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $firstLine = fgets($handle);
    rewind($handle);
    if ($bom === "\xEF\xBB\xBF") fread($handle, 3);
    $sep = (substr_count($firstLine, ';') >= substr_count($firstLine, ',')) ? ';' : ',';

    $header = fgetcsv($handle, 2000, $sep);
    if (!$header) return ['error' => 'Fichier vide.'];

    $header_clean = array_map(fn($h) => trim(strtolower(str_replace(['é','è','ê','ë','î','ï','ô','ù','û','ü','ç','à',' '],['e','e','e','e','i','i','o','u','u','u','c','a','_'], $h))), $header);
    $col = [];
    foreach ($header_clean as $i => $h) $col[$h] = $i;

    $idx_mat = $col['matricule'] ?? null;
    $idx_nom = $col['nom'] ?? null;
    $idx_prenom = $col['prenom'] ?? $col['pr_nom'] ?? null;

    if ($idx_mat === null || $idx_nom === null || $idx_prenom === null) return ['error' => 'Colonnes (Matricule, Nom, Prénom) introuvables.'];

    $inserted = 0; $skipped = 0; $errors = [];
    $default_pw = password_hash('password', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO etudiants (matricule, nom, prenom, date_naissance, email, mot_de_passe, niveau, specialite, section, groupe_td, groupe_tp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    while (($row = fgetcsv($handle, 2000, $sep)) !== false) {
        if (empty(array_filter($row))) { $skipped++; continue; }
        $matricule = trim($row[$idx_mat] ?? '');
        if (empty($matricule) || !is_numeric(substr($matricule, 0, 3))) { $skipped++; continue; }

        $nom = strtoupper(trim($row[$idx_nom] ?? ''));
        $prenom = trim($row[$idx_prenom] ?? '');
        $section = strtoupper(trim($row[$col['section'] ?? null] ?? 'A'));
        
        $palier = strtoupper(trim($row[$col['palier'] ?? null] ?? ''));
        $niveau = '2eme Annee';
        if ($palier === 'L1' || strpos($palier,'1') !== false) $niveau = '1ere Annee';
        elseif ($palier === 'L3' || strpos($palier,'3') !== false) $niveau = '3eme Annee';

        $email = trim($row[$col['email'] ?? null] ?? (strtolower($matricule) . '@etud.usthb.dz'));
        
        try {
            $stmt->execute([
                $matricule, $nom, $prenom, null, $email, $default_pw, $niveau, 
                trim($row[$col['specialite'] ?? null] ?? 'ISIL'), $section,
                (int)($row[$col['groupe_td'] ?? null] ?? 1), (int)($row[$col['groupe_tp'] ?? null] ?? 1)
            ]);
            $inserted++;
        } catch (Exception $e) {
            $errors[] = "Matricule $matricule déjà existant ou erreur.";
            $skipped++;
        }
    }
    fclose($handle);
    return ['inserted' => $inserted, 'skipped' => $skipped, 'errors' => $errors];
}
