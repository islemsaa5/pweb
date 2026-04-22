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
require_once 'includes/csv_handlers.php';
requireRole('admin');

$page_title = 'Gestion des Étudiants';
$message = '';
$error = '';
$import_report = [];

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    $section_filter = $_GET['section'] ?? 'all';
    exportEtudiantsCSV($pdo, $section_filter);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $matricule      = clean($_POST['matricule']);
        $nom            = clean($_POST['nom']);
        $prenom         = clean($_POST['prenom']);
        $date_naissance = clean($_POST['date_naissance']);
        $email          = clean($_POST['email']);
        $niveau         = clean($_POST['niveau']);
        $specialite     = clean($_POST['specialite'] ?? 'ISIL');
        $section        = strtoupper(clean($_POST['section']));
        $groupe_td      = (int)($_POST['groupe_td'] ?? 1);
        $groupe_tp      = (int)($_POST['groupe_tp'] ?? 1);
        $password       = password_hash('password', PASSWORD_BCRYPT);

        if (empty($matricule) || empty($nom) || empty($prenom) || empty($email)) {
            $error = 'Tous les champs obligatoires doivent être remplis.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO etudiants (matricule, nom, prenom, date_naissance, email, mot_de_passe, niveau, specialite, section, groupe_td, groupe_tp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$matricule, $nom, $prenom, $date_naissance ?: null, $email, $password, $niveau, $specialite, $section, $groupe_td, $groupe_tp]);
                $message = "L'étudiant a été ajouté avec succès.";
            } catch (PDOException $e) {
                $error = ($e->getCode() == 23000) ? "Cet email ou matricule existe déjà." : "Erreur : " . $e->getMessage();
            }
        }
    }

    elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
        if ($stmt->execute([$id])) $message = "L'étudiant a été supprimé.";
    }

    elseif ($action === 'import') {
        $result = importEtudiantsCSV($pdo, $_FILES['csv_file'] ?? []);
        if (isset($result['error'])) {
            $error = $result['error'];
        } else {
            $message = $result['inserted'] . " étudiant(s) importé(s) avec succès.";
            $import_report = $result['errors'];
        }
    }
}

$query = "
    SELECT e.*,
    (SELECT AVG(note) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id AND m.semestre = 1) as moy_s1,
    (SELECT AVG(note) FROM notes n JOIN modules m ON n.module_id = m.id WHERE n.etudiant_id = e.id AND m.semestre = 2) as moy_s2,
    (SELECT AVG(note) FROM notes WHERE etudiant_id = e.id) as moy_annuelle,
    (SELECT COUNT(*) FROM notes WHERE etudiant_id = e.id) as nb_notes
    FROM etudiants e
    ORDER BY e.section ASC, e.nom ASC
";
$etudiants = $pdo->query($query)->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="main-content">
    <div class="page-header">
        <h1>Gestion des Étudiants</h1>
        <p>Administration des sections et des résultats académiques</p>
    </div>

    <?php if ($message): ?>
        <div class="msg-success animate-pop"><i class="fa-solid fa-circle-check"></i> <?= $message ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="msg-error animate-pop"><i class="fa-solid fa-circle-xmark"></i> <?= $error ?></div>
    <?php endif; ?>
    <?php if (!empty($import_report)): ?>
        <div class="import-report animate-pop">
            <strong><i class="fa-solid fa-triangle-exclamation"></i> Détails de l'import :</strong>
            <ul><?php foreach ($import_report as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <!-- Barre d'actions -->
    <div class="actions-bar">
        <button class="btn-add" onclick="toggleModal('modalAdd')">
            <i class="fa-solid fa-plus"></i> Nouvel Étudiant
        </button>
        <button class="btn-import" onclick="toggleModal('modalImport')">
            <i class="fa-solid fa-file-arrow-up"></i> Importer CSV
        </button>
        <!-- Export PDF (format officiel USTHB) -->
        <div class="export-dropdown">
            <button class="btn-export-action" id="exportBtn" onclick="toggleDropdown()">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF <i class="fa-solid fa-caret-down"></i>
            </button>
            <div class="dropdown-menu" id="exportDropdown" style="display:none;">
                <a href="liste_section_pdf.php?section=all" target="_blank"><i class="fa-solid fa-users"></i> PDF Ã¢â‚¬â€ Toutes les sections</a>
                <a href="liste_section_pdf.php?section=A" target="_blank"><i class="fa-solid fa-graduation-cap"></i> PDF Ã¢â‚¬â€ Section A</a>
                <a href="liste_section_pdf.php?section=B" target="_blank"><i class="fa-solid fa-graduation-cap"></i> PDF Ã¢â‚¬â€ Section B</a>
                <a href="liste_section_pdf.php?section=C" target="_blank"><i class="fa-solid fa-graduation-cap"></i> PDF Ã¢â‚¬â€ Section C</a>
                <hr style="margin:4px 0; border-color:#e2e8f0;">
                <a href="etudiants.php?action=export&section=all"><i class="fa-solid fa-file-csv"></i> CSV Ã¢â‚¬â€ Toutes sections</a>
                <a href="etudiants.php?action=export&section=A"><i class="fa-solid fa-file-csv"></i> CSV Ã¢â‚¬â€ Section A</a>
                <a href="etudiants.php?action=export&section=B"><i class="fa-solid fa-file-csv"></i> CSV Ã¢â‚¬â€ Section B</a>
                <a href="etudiants.php?action=export&section=C"><i class="fa-solid fa-file-csv"></i> CSV Ã¢â‚¬â€ Section C</a>
            </div>
        </div>
        <div class="badge" style="background:#2c3e80;color:white;margin-left:auto;">
            Total : <?= count($etudiants) ?> étudiants
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Section</th>
                    <th>G.TD</th>
                    <th>G.TP</th>
                    <th>Moy S1</th>
                    <th>Moy S2</th>
                    <th>Moy Ann.</th>
                    <th>État</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $n = 1; foreach ($etudiants as $e): ?>
                <?php
                    $m1 = $e['moy_s1'] !== null ? round($e['moy_s1'], 2) : null;
                    $m2 = $e['moy_s2'] !== null ? round($e['moy_s2'], 2) : null;
                    $annuelle = $e['moy_annuelle'] !== null ? round($e['moy_annuelle'], 2) : null;

                    if ($annuelle === null)      { $etat_class = ''; $etat = 'N/A'; }
                    elseif ($annuelle >= 10)     { $etat_class = 'badge-admis'; $etat = 'ADM'; }
                    else                         { $etat_class = 'badge-ajourne'; $etat = 'AJR'; }

                    $etat_color = ($e['nb_notes'] > 0) ? '#28a745' : '#aaa';
                    $sec = strtolower($e['section'] ?? 'a');
                ?>
                <tr>
                    <td style="text-align:center;color:#888;"><?= $n++ ?></td>
                    <td><span class="badge-code"><?= htmlspecialchars($e['matricule']) ?></span></td>
                    <td><strong><?= htmlspecialchars($e['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                    <td style="text-align:center;"><span class="section-badge sec-<?= $sec ?>"><?= htmlspecialchars(strtoupper($e['section'] ?? '')) ?></span></td>
                    <td style="text-align:center;"><?= $e['groupe_td'] ?? 1 ?></td>
                    <td style="text-align:center;"><?= $e['groupe_tp'] ?? 1 ?></td>
                    <td style="text-align:center;"><?= $m1 ?? '-' ?></td>
                    <td style="text-align:center;"><?= $m2 ?? '-' ?></td>
                    <td style="text-align:center;font-weight:bold;"><?= $annuelle ?? '-' ?></td>
                    <td style="text-align:center;"><span class="badge <?= $etat_class ?>"><?= $etat ?></span></td>
                    <td>
                        <div style="display:flex;gap:5px;justify-content:center;">
                            <a href="releve_notes.php?id=<?= $e['id'] ?>" class="btn-action" title="Relevé de notes"><i class="fa-solid fa-file-invoice"></i></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cet étudiant ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn-action delete" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($etudiants)): ?>
                <tr><td colspan="12" class="empty-row">Aucun étudiant trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajouter -->
<div class="modal-overlay" id="modalAdd" style="display:none;">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Ajouter un étudiant</h3>
            <button class="modal-close" onclick="toggleModal('modalAdd')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Matricule *</label>
                        <input type="text" name="matricule" placeholder="Ex: 242431XXXXXX" required>
                    </div>
                    <div class="form-group">
                        <label>Section *</label>
                        <select name="section">
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C" selected>Section C</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" placeholder="NOM" required>
                    </div>
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" placeholder="Prénom" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Palier / Niveau</label>
                        <select name="niveau">
                            <option value="1ere Annee">L1 - 1ère Année</option>
                            <option value="2eme Annee" selected>L2 - 2ème Année</option>
                            <option value="3eme Annee">L3 - 3ème Année</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Spécialité</label>
                        <input type="text" name="specialite" value="ISIL" placeholder="ISIL">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Groupe TD</label>
                        <input type="number" name="groupe_td" value="1" min="1" max="10">
                    </div>
                    <div class="form-group">
                        <label>Groupe TP</label>
                        <input type="number" name="groupe_tp" value="1" min="1" max="10">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="matricule@etud.usthb.dz">
                </div>
                <div class="form-group">
                    <label>Date de naissance</label>
                    <input type="date" name="date_naissance">
                </div>
                <p style="font-size:12px;color:#888;margin-top:5px;"><i class="fa-solid fa-info-circle"></i> Mot de passe par défaut : <strong>password</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalAdd')">Annuler</button>
                <button type="submit" class="btn-add">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import CSV -->
<div class="modal-overlay" id="modalImport" style="display:none;">
    <div class="modal" style="width:580px; max-width:95%;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-file-arrow-up"></i> Importer une liste étudiants</h3>
            <button class="modal-close" onclick="toggleModal('modalImport')">&times;</button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="action" value="import">

                <!-- Alerte PDF -->
                <div style="background:#fff3cd; border:1px solid #ffc107; border-left:4px solid #fd7e14; border-radius:6px; padding:12px 14px; margin-bottom:15px; font-size:13px;">
                    <strong>Ã¢Å¡Â Ã¯Â¸Â Impossible d'importer un PDF !</strong><br>
                    Les fichiers PDF ne peuvent pas àªtre lus par le système. La liste USTHB est un fichier <strong>Excel (.xlsx)</strong>.
                    Suivez les étapes ci-dessous pour le convertir en CSV importable.
                </div>

                <!-- Guide étape par étape -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:15px;">
                    <p style="font-weight:700; color:#2c3e80; margin-bottom:12px; font-size:13px;">
                        Ã°Å¸â€œâ€¹ Comment importer la liste officielle USTHB ?
                    </p>

                    <div class="import-step">
                        <div class="step-num">1</div>
                        <div class="step-text">
                            <strong>Ouvrez le fichier Excel</strong> reà§u de l'université (liste des étudiants .xlsx)
                        </div>
                    </div>

                    <div class="import-step">
                        <div class="step-num">2</div>
                        <div class="step-text">
                            <strong>Fichier Ã¢â€ â€™ Enregistrer sous</strong> Ã¢â€ â€™ Choisissez le format :
                            <code style="background:#1e293b;color:#7dd3fc;padding:2px 6px;border-radius:3px;font-size:11px;margin-left:4px;">CSV UTF-8 (délimité par des virgules)</code>
                            <br><small style="color:#888;">ou "CSV (séparateur : point-virgule)"</small>
                        </div>
                    </div>

                    <div class="import-step">
                        <div class="step-num">3</div>
                        <div class="step-text">
                            Cliquez <strong>Oui/OK</strong> si Excel demande une confirmation, puis <strong>enregistrez</strong>.
                        </div>
                    </div>

                    <div class="import-step">
                        <div class="step-num">4</div>
                        <div class="step-text">
                            <strong>Uploadez le fichier .csv</strong> ci-dessous. Le système reconnaît automatiquement le format USTHB.
                        </div>
                    </div>

                    <div style="margin-top:10px; background:#e8f5e9; border-radius:6px; padding:8px 12px; font-size:12px; color:#2e7d32;">
                        ✅ <strong>Colonnes reconnues automatiquement :</strong>
                        N°, Palier, Spécialité, <strong>Section</strong>, <strong>Matricule</strong>, <strong>Nom</strong>, <strong>Prénom</strong>, État, Groupe TD, Groupe TP
                    </div>
                </div>

                <!-- Zone upload -->
                <div class="form-group">
                    <label style="font-weight:600; color:#333;">Fichier CSV (converti depuis Excel) *</label>
                    <div class="file-drop-zone" id="dropZone" onclick="document.getElementById('csvInput').click()">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:32px;color:#2c3e80;margin-bottom:8px;display:block;"></i>
                        <p><strong>Cliquez ici</strong> ou glissez-déposez votre fichier CSV</p>
                        <small style="color:#aaa;">.csv uniquement Ã¢â‚¬â€ converti depuis Excel USTHB</small>
                        <br>
                        <span id="fileName" style="font-size:13px;color:#2c3e80;font-weight:600;margin-top:5px;display:block;"></span>
                    </div>
                    <input type="file" id="csvInput" name="csv_file" accept=".csv,.txt" required style="display:none;" onchange="showFileName(this)">
                </div>

                <!-- Lien modèle CSV -->
                <div style="margin-top:8px; font-size:12px;">
                    <a href="etudiants.php?action=export&section=all" class="csv-template-link">
                        <i class="fa-solid fa-download"></i> Télécharger un modèle CSV basé sur les données actuelles
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="toggleModal('modalImport')">Annuler</button>
                <button type="submit" class="btn-add"><i class="fa-solid fa-upload"></i> Lancer l'import</button>
            </div>
        </form>
    </div>
</div>


<script>
function toggleModal(id) {
    document.getElementById(id).style.display = (document.getElementById(id).style.display === 'none') ? 'flex' : 'none';
}
function toggleDropdown() {
    const d = document.getElementById('exportDropdown');
    d.style.display = (d.style.display === 'none') ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.export-dropdown')) {
        const d = document.getElementById('exportDropdown');
        if (d) d.style.display = 'none';
    }
});
function showFileName(input) {
    const name = input.files[0]?.name || '';
    document.getElementById('fileName').textContent = name ? 'Ã°Å¸â€œâ€ž ' + name : '';
}

const dropZone = document.getElementById('dropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('csvInput').files = e.dataTransfer.files;
            document.getElementById('fileName').textContent = 'Ã°Å¸â€œâ€ž ' + file.name;
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>
